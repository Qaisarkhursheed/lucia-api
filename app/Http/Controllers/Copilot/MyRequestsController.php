<?php

namespace App\Http\Controllers\Copilot;

use App\ModelsExtended\User;
use Illuminate\Http\Request;
use App\Http\Responses\OkResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\AdvisorRequestArchived;
use App\Models\ActivityType;
use App\ModelsExtended\AdvisorRequest;
use Illuminate\Database\Eloquent\Builder;
use App\ModelsExtended\AdvisorRequestStatus;
use App\ModelsExtended\AdvisorAssignedCopilot;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Mail\Copilot\RequestArchivedMail;
use App\ModelsExtended\CopilotInfo;
use App\Repositories\Pusher\PushNotifications\NewRequestAvailablePushNotification;
use Exception;
use Illuminate\Console\Command;


class MyRequestsController extends Controller
{
    use ChatRequestsViewListTrait;

    /**
     * @var Authenticatable|User
     */
    private $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function fetchAll()
    {
        return $this->showList();
    }
    public function fetchAllNotAccepted()
    {
        return $this->showNotAcceptedList();
    }

    /**
     * Mark task archive
     */
    public function archive(Request $request)
    {

        $this->validate($request, [
            'advisor_request_id' => 'required|int',
            'copilot_id' => 'required|int',
        ]);

        $getRequest = AdvisorRequest::find($request->advisor_request_id);
        if ($getRequest) {

            if (!AdvisorRequestArchived::where(['copilot_id' => $request->copilot_id, 'advisor_request_id' => $request->advisor_request_id])->exists()) {
                $AdvisorRequestArchived = new AdvisorRequestArchived();
                $AdvisorRequestArchived->copilot_id = $request->copilot_id;
                $AdvisorRequestArchived->advisor_request_id = $request->advisor_request_id;
                if ($AdvisorRequestArchived->save()) {

                    if($getRequest->advisor_assigned_copilot())
                    {
                        $this->sendArchivedRequestEmailToAdvisor($request->copilot_id,$request->advisor_request_id);
                    }
                    return new OkResponse(['Request succesfully archived.']);

                } else {
                    throw new \Exception('System error please try again.');
                }
            } else {
                return new OkResponse(['Request already archived.']);
            }
        }else{
            return new OkResponse(['Request does not exist.']);
        }
    }

    /**
     * @return Builder
     */
    private function getDataQuery(): Builder
    {
        return AdvisorRequest::with("advisor_request_status", "advisor_request_attachments")
            ->whereHas('advisor_assigned_copilot', function (Builder $builder) {
                $builder->where('advisor_assigned_copilot.copilot_id', $this->user->id);
            })->whereNotIn('id', function ($query) {
                $query->select('advisor_request_id')->from('advisor_request_archived')->where('copilot_id',$this->user->id);
            })
            ->whereIn("advisor_request_status_id", [
                AdvisorRequestStatus::ACCEPTED,
                AdvisorRequestStatus::COMPLETED,
                AdvisorRequestStatus::REFUNDED,
                AdvisorRequestStatus::PENDING,
            ]);
    }

    private function sendArchivedRequestEmailToAdvisor($copilot_id,$request_id){
        $AdvisorAssignedCopilot = AdvisorAssignedCopilot::where(['copilot_id'=>$copilot_id,'advisor_request_id'=>$request_id])->first();

        if($AdvisorAssignedCopilot){

            $AdvisorRequest = AdvisorRequest::find($request_id);
            $copilot = User::find($copilot_id);

            $user = User::find($AdvisorRequest->created_by_id);
            Mail::send( (new RequestArchivedMail($AdvisorRequest,$user,$copilot)));

        }


    }
    private function showNotAcceptedList()
    {
        return AdvisorRequest::with("advisor_request_status", "advisor_request_attachments")
            ->whereHas('advisor_assigned_copilot', function (Builder $builder) {
                $builder->where('advisor_assigned_copilot.copilot_id', $this->user->id);
            })->whereNotIn('id', function ($query) {
                $query->select('advisor_request_id')->from('advisor_request_archived')->where('copilot_id',$this->user->id);
            })
            ->whereIn("advisor_request_status_id", [
                AdvisorRequestStatus::PAID
            ])
            ->orderBy("advisor_request.id")->get()->map( function(AdvisorRequest $request) {
                return
                    array_merge(
                        $request->presentForDev(),
                        [
                            'has_unread_messages' => $request->advisor_chats_count > 0,
                            'unread_messages' => $request->advisor_chats_count,
                        ]
                    );
            });;
    }
    public function submitForApproval(Request $request){
        $this->validate($request, [
            'advisor_request_id' => 'required|int',
        ]);
        $advisorRequest = AdvisorRequest::find($request->get('advisor_request_id'));
            if( $advisorRequest->advisor_request_status_id !== AdvisorRequestStatus::ACCEPTED )
                throw new \Exception( 'You can not mark this request as completed in this request status!' );
        try {
            $advisorRequest
                ->update([
                    'advisor_request_status_id' => AdvisorRequestStatus::PENDING
                ]);
            $advisorRequest->createActivity("Request marked completed!", ActivityType::ADVISOR_REQUEST, $advisorRequest->created_by_id);
             return new OkResponse(["status"=>true, "message" => 'Request has been Succesfully submitted for approval']);
            // send mail to advisor
            // Mail::send( new AdvisorRequestForApprobalMail( $advisorRequest ) );
        }catch (\Exception $exception){
        }


    }
}
