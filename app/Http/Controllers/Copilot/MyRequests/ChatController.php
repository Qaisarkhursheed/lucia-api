<?php

namespace App\Http\Controllers\Copilot\MyRequests;

use App\Console\Commands\Payments\DisburseCopilotPayment;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Copilot\RequestMailResponseController;
use App\Http\Controllers\Enhancers\HasRouteModelControllerTrait;
use App\Mail\Agent\AdvisorRequestCompletedMail;
use App\Mail\Copilot\RequestSentBackToPoolMail;
use App\Mail\NotifyAdminRequestRefunded;
use App\ModelsExtended\AdvisorChat;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\AdvisorRequestStatus;
use App\ModelsExtended\ChatContentType;
use App\ModelsExtended\CopilotDuty;
use App\ModelsExtended\User;
use App\Repositories\Pusher\PushNotifications\AdvisorChatMessageReceivePushNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\ActivityType;
use App\Mail\NewConciergeMessageMail;
use App\Traits\ZoomMeetingTrait;
use App\Models\Meeting;

/**
 * @property AdvisorRequest $model
 */
class ChatController extends Controller
{
    use HasRouteModelControllerTrait, ZoomMeetingTrait;

    /**
     * @var Authenticatable|User
     */
    protected $user;
    const MEETING_TYPE_INSTANT = 1;
    const MEETING_TYPE_SCHEDULE = 2;
    const MEETING_TYPE_RECURRING = 3;
    const MEETING_TYPE_FIXED_RECURRING_FIXED = 8;

    /**
     * @throws \App\Exceptions\RecordNotFoundException
     */
    public function __construct()
    {
        $this->user = auth()->user();
        $this->invokeLoadRouteModelFunction ( "advisor_id", "advisor_request.id" );
    }

    /**
     * @return array
     */
    public function fetch()
    {
        return [
            "request" => $this->model->presentForDev(),
            "owner" => Arr::only( $this->model->user->presentForDev() , [
                "id",
                "name",
                "email",
                "first_name",
                "last_name",
                "profile_image_url",
                "phone",
                "location",
                "agency_name",
                "job_title",
                "linkedin_url",
                "agency_usage_mode_id",
                "agency_usage_mode",
            ]),
            "copilot" => $this->presentCopilot(optional($this->model->advisor_assigned_copilot)->user),
        ];
    }

    /**
     * @return Builder
     */
    public function getDataQuery(): Builder
    {
        return AdvisorRequest::with( "advisor_request_status", "advisor_request_attachments")
            ->whereHas('advisor_assigned_copilot', function (Builder $builder){
                $builder->where( 'advisor_assigned_copilot.copilot_id', $this->user->id );
            })
            ->whereIn("advisor_request_status_id", [
                AdvisorRequestStatus::ACCEPTED,
                AdvisorRequestStatus::COMPLETED,
                AdvisorRequestStatus::REFUNDED,
                AdvisorRequestStatus::PENDING,
            ])
            ->orderByDesc("created_at");
    }

    /**
     * @param User|null $copilot
     * @return array
     */
    private function presentCopilot(?User $copilot): array
    {
        if( !$copilot ) return [];
        return array_merge(
            [
              "overall_reviews" => optional($copilot->average_feedback)->presentForDev(),
              "ratings" => $copilot->ratings->sortByDesc('average_rating')->map->presentForDev(),
            ],
            Arr::only( $copilot->presentForDev() , [
                "id",
                "name",
                "email",
                "first_name",
                "last_name",
                "profile_image_url",
                "phone",
                "location",
                "job_title",
                "linkedin_url",
            ]),
            [
                "co_pilot_duties" => $copilot->copilot_duties->map( fn( CopilotDuty $duty ) => $duty->advisor_request_type->description )
            ]
        );
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function checkCanChat(): ChatController
    {
        if( $this->model->advisor_request_status_id !== AdvisorRequestStatus::ACCEPTED )
            throw new \Exception( 'You can not initiate chat in this request status!' );

        return $this;
    }

    /**
     * @param Request $request
     * @return AdvisorChat|\Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function sendChatMessage(Request $request)
    {
        $this->validatedRules([
            'plain_text' => 'required|min:1|max:8000|string'
        ]);

        $this->model->createActivity($request->input( 'plain_text' ),ActivityType::MESSAGE,
        ($this->user->id == $this->model->created_by_id ?
        $this->model->advisor_assigned_copilot->copilot_id : $this->model->created_by_id) // receiver id
    );
        $this->model->refresh();

        return $this->createChatCore(ChatContentType::TEXT, $request->input( 'plain_text' ), null);

    }

    /**
     * @param Request $request
     * @return AdvisorChat|\Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function sendFile(Request $request)
    {
        $this->validatedRules([
            'document' => 'required|file|max:20000',    // 20MB
        ]);

        $document = $request->file( 'document' );
        $documentSize = $document->getSize();

        $document_relative_url =  AdvisorChat::generateRelativePath( $document,  $this->model );
        Storage::cloud()->put( $document_relative_url, $document->getContent() );


        return $this->createChatCore(ChatContentType::DOCUMENT, $document->getClientOriginalName(), $document_relative_url,null,$documentSize);
    }

    /**
     * @param int $chat_content_type_id
     * @param string $plain_text
     * @param string|null $document_relative_url
     * @return \Illuminate\Database\Eloquent\Model|AdvisorChat
     * @throws \Exception
     */
    private function createChatCore( int $chat_content_type_id, string $plain_text,?string $document_relative_url, $meeting_id= null, $file_size= null)
    {

        $chat = $this->checkCanChat()->model->advisor_chats()->create([
            'chat_content_type_id' => $chat_content_type_id,
            'advisor_request_id' => $this->model->id,
            'sender_id' => $this->user->id,
            'receiver_id' => $this->user->id == $this->model->created_by_id ?
                $this->model->advisor_assigned_copilot->copilot_id : $this->model->created_by_id,
            'plain_text' => $plain_text,
            'document_relative_url' => $document_relative_url,
            'seen' => false,
            'meeting_id' => (isset($meeting_id) ? $meeting_id : null),
            'file_size' => (isset($file_size) ? $file_size : null),
        ]);
        Mail::send( new NewConciergeMessageMail( $chat ) );

        dispatch( new AdvisorChatMessageReceivePushNotification( $chat ) );
        broadcast(new \App\Events\ConciergeLiveChatBroadcastEvent ($chat));

        return $chat;
    }

    public function listChats()
    {
        $this->model->advisor_chats()
            ->where("receiver_id",  $this->user->id)
            ->update([
                'seen' => true
            ]);

        return $this->model->advisor_chats()->orderBy("created_at")
            ->get()
            ->map( function ( AdvisorChat $chat){
                return array_merge( $chat->presentForDev(),
                    [
                        "logged_in_user_is_sender" => $chat->sender_id === $this->user->id
                    ]
                );
            });
    }

    public function markAsCompleted()
    {
        if( $this->model->advisor_request_status_id !== AdvisorRequestStatus::ACCEPTED )
            throw new \Exception( 'You can not mark this request as completed in this request status!' );

        if( $this->model->task_completed_count !== $this->model->advisor_request_tasks->count() )
            throw new \Exception("Please, complete all tasks in this request first!");

        $this->model
            ->update([
                'advisor_request_status_id' => AdvisorRequestStatus::COMPLETED,
                'completed_at'=>\Carbon\Carbon::now(),
            ]);

        $this->model->createActivity("Request marked completed!", ActivityType::ADVISOR_REQUEST, $this->model->created_by_id);

        // send rating info
        Mail::send( new AdvisorRequestCompletedMail( $this->model ) );

        // send payment
        try {
            DisburseCopilotPayment::disburse($this->model);
        }catch (\Exception $exception){
            // can't disburse yet
        }

        return $this->fetch();
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function markTaskCompleted(Request $request)
    {
        $this->validatedRules([
            "task_id" => "required|numeric|exists:advisor_request_task,id"
        ]);

        if( $this->model->advisor_request_status_id !== AdvisorRequestStatus::ACCEPTED )
            throw new \Exception( 'You can not perform this action on this request in its current status!' );

        $task = $this->model->getTask($request->input("task_id"));

        if(!$task) throw new \Exception('The task does not belong to this request!');

        if( !$task->completed )
        {
            $task->completed = true;
            $task->update();
            $this->model->createActivity( "Task ".  $task->title . " completed", ActivityType::ADVISOR_REQUEST, $this->model->created_by_id);
            $this->model->refresh();
        }

        return $this->fetch();
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function markTaskUncompleted(Request $request)
    {
        $this->validatedRules([
            "task_id" => "required|numeric|exists:advisor_request_task,id"
        ]);

        if( $this->model->advisor_request_status_id !== AdvisorRequestStatus::ACCEPTED )
            throw new \Exception( 'You can not perform this action on this request in its current status!' );

        $task = $this->model->getTask($request->input("task_id"));

        if(!$task) throw new \Exception('The task does not belong to this request!');

        if( $task->completed )
        {
            $task->completed = false;
            $task->update();
            $this->model->createActivity($task->advisor_request_type->description . " marked uncompleted",ActivityType::ADVISOR_REQUEST,$this->model->created_by_id);
            $this->model->refresh();
        }

        return $this->fetch();
    }

   /**
    * @return mixed
    * @throws \Stripe\Exception\ApiErrorException
    */
   public function refundPayment()
   {
       // make this atomic
       // ----------------------------------------------------
    //    return $this->runInALock('paying-advisor-' . $this->model->id, function () {

           if ($this->model->advisor_request_status_id !== AdvisorRequestStatus::ACCEPTED)
               throw new \Exception('You can not create refund on this advisor request because it is not in ACCEPTED status anymore.');

           $this->model->refundCharge();

        //    return $this->fetch();
    //    });
   }

    /**
     * @return mixed
     * @throws \Stripe\Exception\ApiErrorException|\Exception
     */
    public function returnToPool()
    {
        if ($this->model->advisor_request_status_id !== AdvisorRequestStatus::ACCEPTED)
            throw new \Exception('You can not return this advisor request because it is not in ACCEPTED status anymore.');

        $senderName = $this->model->advisor_assigned_copilot->user->first_name;
        // $this->refundPayment(); we dont wanna give refund once user will accept it.. We will goto customer and see what he says
        // or we can assign the request to any other copilot!


        Mail::send( new RequestSentBackToPoolMail( $this->model, $senderName ) );
        // Mail::send( new NotifyAdminRequestRefunded( $this->model, $this->user ) );
        RequestMailResponseController::pushRequestBackToPool( $this->model );
        \Log::info("Cancelled request {$this->model} has been cancelled by {$this->user->first_name } {$this->user->last_name }");

        return $this->fetch();
    }

    /**
     * @return mixed
     * @throws \Stripe\Exception\ApiErrorException|\Exception
     */
    public function createZoomMeeting(Request $request)
    {
        $start_time = \Carbon\Carbon::now();
        $start_time = $start_time->toDateTimeString();
        $data = array(
            "topic" => "Meeting",
            "start_time" => $start_time,
            "agenda" => "Lucia - Meeting",
            "duration" => 30,
            "host_video" => isset($request->host_video) ?  true : false,
            "participant_video" => false,
            'advisor_request_id' => $this->model->id,
        );
        $meeting = $this->create($data);
        if(isset($meeting['success']))
        {
            $this->model->createActivity($meeting['data']['join_url'],ActivityType::MEETING,
            ($this->user->id == $this->model->created_by_id ?
            $this->model->advisor_assigned_copilot->copilot_id : $this->model->created_by_id) // receiver id
        );
            $this->model->refresh();
            return $this->createChatCore(ChatContentType::MEETING, $meeting['data']['join_url'], null, $meeting['meeting_id']);
        }
        return $this->fetch();
    }
    public function declineZoomMeeting(Request $request)
    {
        $meeting = Meeting::find($request->meeting_id);
        if($meeting)
        {

        $zoomResponse = $this->delete($meeting->meeting_id);
        if(isset($zoomResponse['success']))
        {
            $meeting->status = "canceled ";
            $meeting->save();

            $this->model->createActivity("The meeting has been cancelled!",ActivityType::MEETING,
            ($this->user->id == $this->model->created_by_id ?
            $this->model->advisor_assigned_copilot->copilot_id : $this->model->created_by_id) // receiver id
        );
            $this->model->refresh();
        }
        return $this->fetch();
        }

    }
}
