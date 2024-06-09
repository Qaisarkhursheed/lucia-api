<?php

namespace App\Http\Controllers\Agent\AdvisorRequests;

use App\Console\Commands\Payments\AdvisorRequestsPaymentMonitor;
use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Responses\OkResponse;
use App\Mail\Copilot\NewRequestReceivedMail;
use App\Mail\NewHourlyRequestReceivedMail;
use App\Mail\NotifyAdminRequestRefunded;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\AdvisorRequestAttachment;
use App\ModelsExtended\AdvisorRequestStatus;
use App\ModelsExtended\AdvisorRequestType;
use App\ModelsExtended\RequestAvailableDiscount;
use App\ModelsExtended\StripePaymentIntent;
use App\ModelsExtended\User;
use App\Repositories\Pusher\PushNotifications\SpecificRequestReceivedPushNotification;
use App\Repositories\Stripe\StripeConnectSDK;
use App\Repositories\Stripe\StripeSubscriptionSDK;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use App\ModelsExtended\PreferredPartners;
use App\Models\AdvisorTaskCategory;
use App\Models\AdvisorRequestArchived;
use App\Models\ActivityType;
use App\Models\FavoriteCopilot as FavoriteCopilot;
use App\ModelsExtended\AccountStatus;
use App\Models\Testimonials;
use App\Models\AdvisorChat;
use App\ModelsExtended\Role;
use Illuminate\Support\Arr;

/**
 * @property AdvisorRequest $model
 */
class AdvisorController extends CRUDEnabledController
{
    private const MAXIMUM_DOCUMENTS = 10;
    /**
     * @var Authenticatable|User
     */
    private $user;

    public function __construct()
    {
        $this->user = auth()->user();
        parent::__construct( "advisor_id", "advisor_request.id" );
    }

    public function getDataQuery(): Builder
    {
        return AdvisorRequest::with( "advisor_request_status", "advisor_request_attachments" )
            ->where("created_by_id", $this->user->id );
    }

    public function getCommonRules()
    {
        return [
            'copilot_id' => 'filled|numeric|exists:users,id',
            'itinerary_id' => 'filled|numeric|exists:itinerary,id',
            'request_type' => 'required',
            'deadline_time' => 'filled|date_format:h\:i\ A',
            'deadline_day' => 'filled|date_format:Y-m-d|after_or_equal:today',

            'request_title' => 'required|string|max:300',

           // 'tasks' => 'required|array|min:1',
            //'tasks.*.explanation' => 'nullable|array|max:500',
          //  'tasks.*.explanation.*' => 'nullable|string|max:500',

            'notes' => 'nullable|string|max:5000',

            'attachments' => 'filled|array|max:'. self::MAXIMUM_DOCUMENTS,
            'attachments.*' => 'filled|file|max:20000'
        ];
    }

    public function fetchAll()
    {

        $response = array();
        $response['InProgressRequest'] = $this->getMyRequest(AdvisorRequestStatus::ACCEPTED);
        $response['CompletedRequest']  = $this->getMyRequest(AdvisorRequestStatus::COMPLETED);
        $response['RefundRequest']    = $this->getMyRequest(AdvisorRequestStatus::REFUNDED);
        $response['OpenRequest']       = $this->getMyRequest(AdvisorRequestStatus::PAID);
        $response['CancelledRequest']       = $this->getMyRequest(AdvisorRequestStatus::CANCELLED);

        return $response;


        // return $this->getDataQuery()
        //     ->whereNotIn("advisor_request.advisor_request_status_id", [
        //         AdvisorRequestStatus::DRAFT,  // Hide for now
        //     ])
        //     // ACCEPTED, PAID, then others
        //     ->orderByRaw( "if(advisor_request_status_id=3, id, if(advisor_request_status_id=2, 50000+id, 100000+id ) )" )
        //     ->get()
        //     ->map->presentForDev();
    }
    public function fetchAllRequests(){
        return $this->getDataQuery()
        ->leftJoin(DB::raw('(SELECT advisor_request_id, MAX(created_at) AS latest_message FROM advisor_chat GROUP BY advisor_request_id) AS ac'), 'advisor_request.id', '=', 'ac.advisor_request_id')
        ->whereNotIn("advisor_request.advisor_request_status_id", [
            AdvisorRequestStatus::DRAFT,  // Hide for now
            AdvisorRequestStatus::PAID,  // Hide for now
        ])
        ->orderByRaw('CASE WHEN ac.latest_message IS NULL THEN 1 ELSE 0 END, ac.latest_message DESC')
        ->orderBy('advisor_request.created_at', 'DESC')
        // ACCEPTED, PAID, then others
        // ->orderByRaw( "if(advisor_request_status_id=3, id, if(advisor_request_status_id=2, 50000+id, 100000+id ) )" )
        ->get()
        ->map->presentForDev();
    }

    public function fetchPartners(){

        return PreferredPartners::all();
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        if( $this->model->advisor_request_status_id !== AdvisorRequestStatus::DRAFT )
            throw new \Exception( 'You can not delete this advisor request because it is not in draft status anymore.' );

        Storage::cloud()->deleteDirectory($this->model->getFolderStorageRelativePath());
        $this->model->delete();

        return new OkResponse( );
    }

    /**
     * @param Request $request
     * @return OkResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function applyDiscount(Request $request): OkResponse
    {
        $this->validatedRules([
            "discount_code" => "required|string|min:2"
        ]);

        $discount_code = $request->input("discount_code");

        // check for validity and amount to deduct
        $d = RequestAvailableDiscount::getAvailableDiscount($discount_code, $this->model );
        // ------------

        if( $this->model->advisor_request_status_id !== AdvisorRequestStatus::DRAFT )
            throw new \Exception( 'You can not apply discount on this advisor request because it is not in draft status anymore.' );

        $this->model->discount_code = $discount_code;
        $this->model->discount = $d->discount;
        $this->model->recalculateTotalAmount();
        $this->model->refresh();

        return new OkResponse( $this->model->presentForDev() );
    }

    /**
     * @return OkResponse
     * @throws \Exception
     */
    public function removeDiscount(): OkResponse
    {
        if( $this->model->advisor_request_status_id !== AdvisorRequestStatus::DRAFT )
            throw new \Exception( 'You can remove discount on this advisor request because it is not in draft status anymore.' );

        $this->model->discount_code = null;
        $this->model->discount = 0;
        $this->model->recalculateTotalAmount();
        $this->model->refresh();

        return new OkResponse( $this->model->presentForDev() );
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function store( Request $request )
    {
        $this->validatedRules($this->getCommonRules());

        $copilot = null;
        if( $request->has('copilot_id') )
        {
            $copilot = User::getById($request->input('copilot_id'));
            if( !$copilot->isCopilot() ) throw new \Exception( "The user you selected is not a copilot!" );
        }

        return DB::transaction( function ( ) use ( $request, $copilot ){
            $this->createAdvisorRequest($request)
                    ->assignCopilot( $copilot )
                    ->createTasks( $request )
                    ->createAttachments( $request );

            $this->model->recalculateTotalAmount();

            return $this->fetch();
        });
    }

    /**
     * @param Request $request
     * @return AdvisorController
     */
    private function createAdvisorRequest(Request $request): AdvisorController
    {
        $deadline = null;


        if( $request->has('deadline_day') )
        {
            $deadline = Carbon::createFromFormat( 'Y-m-d', $request->input('deadline_day' )  );
            if( $request->has('deadline_time') )
            {
                $deadline_time = Carbon::createFromTimeString( $request->input('deadline_time' ) );
                $deadline =  $deadline->clone()->setTimeFrom( $deadline_time );
            }
            $deadline =  $deadline->fromPreferredTimezoneToAppTimezone();
        }

        $this->model = $this->user->advisor_requests()->create( [
            'deadline' => $deadline,
            'advisor_request_status_id' => AdvisorRequestStatus::DRAFT,
          //  'advisor_request_type_id' => $request->input( 'advisor_request_type_id' ),
            'itinerary_id' => $request->input( 'itinerary_id' ),
            'notes' => $request->input( 'description' ),
            'request_title' => $request->input( 'request_title' ),
            'hourly_rate' => $request->input( 'hourly_rate' ),
            'request_type' => $request->input( 'request_type' ),
            'discount' => 0,
//            'amount' => AdvisorRequestType::find($request->input( 'advisor_request_type_id' ))->amount,
//            'amount' => $request->input( 'amount' ),
        ] );
        if($request->request_type)
        {
            if($request->request_type == 2)
            {
                sendAlertMessageToSlack("Hey, {$this->user->first_name} {$this->user->last_name} draft new hourly request on lucia. (Its not Paid yet)");
            }
        }
        return $this;
    }

    /**
     * @param Request $request
     * @return AdvisorController
     */
    private function createAttachments(Request $request): AdvisorController
    {
        if( $request->has( "attachments" ) )
        {
            foreach ( $request->file( 'attachments' ) as $attachment )
            {
                $document_relative_url = AdvisorRequestAttachment::generateRelativePath( $attachment, $this->model );
                Storage::cloud()->put( $document_relative_url, $attachment->getContent() );

                $this->model->advisor_request_attachments()->create([
                    'document_relative_url' => $document_relative_url,
                    'name' => $attachment->getClientOriginalName(),
                ]);
            }
        }

        return $this;
    }

    /**
     * @param User|null $copilot
     * @return $this
     */
    private function assignCopilot(?User $copilot): AdvisorController
    {
        if( $copilot )
        {
            $this->model->advisor_assigned_copilot()->create([
                'copilot_id' => $copilot->id
            ]);
            $this->model->createActivity("Invited for the request".$this->model->request_title,ActivityType::ADVISOR_REQUEST, $copilot->id);
            $this->model->refresh();
            // $get_advisor_request = $this->model->advisor_assigned_copilot()->first();

            // $request_data =   AdvisorRequest::with( "advisor_request_status","advisor_assigned_copilot", "advisor_request_attachments","user" )->where('id',$get_advisor_request->advisor_request_id)->first();

            // // send email
            // Mail::send( new NewRequestReceivedMail ( $request_data ) );
        }
        return $this;
    }

    /**
     * @param Request $request
     * @return $this
     */
    private function createTasks(Request $request): AdvisorController
    {
        $taskIndex = 0;
        //foreach ( $request->input( 'tasks' ) as $task )
       // {
           $taskCreated  = $this->model->advisor_request_tasks()->create([
            'explanation' =>  $request->input('description'),
            'title' => $request->input('request_title'),
            'completed' => false,
            'amount' => $request->input('amount'),
            // 'categories'=>$categories,
        ]);

        // if(isset($task['categories'])){
        //     // $categories = $task['categories']?implode(',',$task['categories']):'';
        //     foreach( $task['categories'] as $category_id )
        //     {
        //         AdvisorTaskCategory::create([
        //             'advisor_request_task_id' => $taskCreated->id,
        //             'category_id' => $category_id,

        //         ]);
        //     }
        // }





        // return $this;
   // }

            //  $type = AdvisorRequestType::getById($task[ 'advisor_request_type_id']);
            // if($request->has("tasks.$taskIndex.explanation"))
            // {
            //     foreach ( $request->input( "tasks.$taskIndex.explanation" ) as $explanation )
            //         $this->model->advisor_request_tasks()->create([
            //         'explanation' =>  $explanation,
            //         'advisor_request_type_id' => $type->id,
            //         'completed' => false,
            //         'amount' => $type->amount
            //     ]);
            // }else{
            //     $this->model->advisor_request_tasks()->create([
            //         'explanation' =>  null,
            //         'advisor_request_type_id' => $type->id,
            //         'completed' => false,
            //         'amount' => $type->amount
            //     ]);
            // }
            // $taskIndex++;

        return $this;
    }

    public function fetch()
    {
        return new OkResponse( $this->model->presentForDev() );
    }

//    /**
//     * @param Request $request
//     * @return mixed
//     * @throws \Illuminate\Validation\ValidationException
//     * @throws \Exception
//     */
//    public function pay(Request $request)
//    {
//        $this->validatedRules([
//            'payment_method' => ['required', Rule::in('card', 'payment_token')],
//        ]);
//
//        // make this atomic
//        // ----------------------------------------------------
//        return $this->runInALock('paying-advisor-' . $this->model->id,
//            function () use ($request) {
//
//                if ($this->model->advisor_request_status_id !== AdvisorRequestStatus::DRAFT)
//                    throw new \Exception('You can not make payment on this advisor request because it is not in draft status anymore.');
//
//                try {
//
//                    $card_token = '';
//                    if ($request->input('payment_method') === 'card')
//                        $card_token = $this->generateCardToken($request);
//                    else if ($request->input('payment_method') === 'payment_token')
//                    {
//                        $this->validatedRules([
//                            'payment_token' => 'required|string|min:3',
//                        ]);
//                        $card_token = $request->input('payment_token');
//                    }
//
//                    $SDK = new StripeSubscriptionSDK();
//
//                    $charge = $SDK->chargeCard($this->model->total_amount, $card_token,
//                        sprintf("Advisor request payment by %s (%s) for #%s",
//                            $this->user->name, $this->user->email, $this->model->id
//                        )
//                    );
//
//                    $this->model->advisor_request_payment()->create([
//                        'stripe_payment_info'=> $charge->toArray(),
//                        'amount' => $this->model->total_amount,
//                        'stripe_payment_intent_id' => null,
//                        'stripe_charge_id' => $charge->id,
//                        'stripe_refund_id' => null
//                    ]);
//
//                    $this->model->update([
//                        'advisor_request_status_id' => AdvisorRequestStatus::PAID,
//                    ]);
//
//                    return $this->fetch();
//                } catch (ValidationException $exception) { throw $exception; }
//                catch (\Exception $exception) {
//                    Log::error($exception->getMessage(), $exception->getTrace());
//                    throw new \Exception("Sorry, your checkout could not be created! Please, try again later", 0, $exception);
//                }
//            });
//    }

    /**
     * @return mixed
     * @throws ApiErrorException
     * @throws \Exception
     */
    public function payUsingIntent(Request $request)
    {
        // make this atomic
        // ----------------------------------------------------
        return $this->runInALock('paying-advisor-' . $this->model->id, function () {

                if ($this->model->advisor_request_status_id !== AdvisorRequestStatus::DRAFT)
                    throw new \Exception('You can not make payment on this advisor request because it is not in draft status anymore.');

                try {

                    $SDK = new StripeConnectSDK();

//                      $this->user->user_stripe_account->getStripeDefaultSourceAttribute()
                    $intent = $SDK->createPaymentIntent( $this->model->total_amount,
                        $this->user->user_stripe_account->getStripeCustomerIdAttribute(),
                        false, null
                    );


                    $stripePayment = StripePaymentIntent::create([
                        'user_id' => $this->user->id,
                        'succeeded' => false,
                        'stripe_response' =>  $intent->toArray()
                    ]);

                    $this->model->advisor_request_payment()->updateOrCreate(
                        [
                            "advisor_request_id" => $this->model->id
                        ],
                        [
                        'stripe_payment_info'=> null,
                        'amount' => $this->model->total_amount,
                        'stripe_payment_intent_id' => $stripePayment->id,
                        'stripe_charge_id' => null,
                        'stripe_refund_id' => null
                    ]);
                    return [
                        'clientSecret' => $intent->client_secret,
                        'stripe_key' => env( 'STRIPE_PUBLIC_KEY' ),
                    ];

                } catch (ValidationException $exception) { throw $exception; }
                catch (\Exception $exception) {
                    Log::error($exception->getMessage(), $exception->getTrace());
                    throw new \Exception("Sorry, your intent could not be created! Please, try again later", 0, $exception);
                }
            });
    }

    /**
     * @return mixed
     * @throws ApiErrorException
     * @throws \Exception
     */
    public function payUsingStoredPayment(Request $request)
    {
        $this->validatedRules([
            'stripe_token_id' => 'required|string',
        ]);

        // make this atomic
        // ----------------------------------------------------
        // return $this->runInALock('paying-advisor-' . $this->model->id, function () {

                if ($this->model->advisor_request_status_id !== AdvisorRequestStatus::DRAFT)
                    throw new \Exception('You can not make payment on this advisor request because it is not in draft status anymore.');

                try {

                    $SDK = new StripeConnectSDK();

                    $intent = $SDK->createPaymentIntent( $this->model->total_amount,
                        $this->user->user_stripe_account->getStripeCustomerIdAttribute(),
                        true, \request('stripe_token_id')
                    );

                    if( $intent->status !== "succeeded" ) throw new \Exception("Payment failed!");

                    $stripePayment = StripePaymentIntent::create([
                        'user_id' => $this->user->id,
                        "stripe_response" => $intent->toArray(),
                        "succeeded" => $intent->status == "succeeded",
                    ]);

                    $this->model->advisor_request_payment()->updateOrCreate(
                        [
                            "advisor_request_id" => $this->model->id
                        ],
                        [
                        'stripe_payment_info'=> null,
                        'amount' => ($this->model->total_amount != 0) ? $this->model->total_amount : 1  ,
                        'stripe_payment_intent_id' => $stripePayment->id,
                        'stripe_charge_id' => $stripePayment->getStripeChargeIdAttribute(),
                        'stripe_refund_id' => null
                    ]);

                    AdvisorRequestsPaymentMonitor::completeIntentPayment($this->model);
                    if ($this->model->advisor_request_status_id == AdvisorRequestStatus::PAID)
                    {
                        $this->notifyCopilot();
                    }
                    return $this->fetch();

                } catch (ValidationException $exception) { throw $exception; }
                catch (\Exception $exception) {
                    Log::error($exception->getMessage(), $exception->getTrace());
                    throw $exception;
                }
            // });
    }

    /**
     * @return mixed
     * @throws ApiErrorException
     * @throws \Exception
     */
    public function completeIntentPayment()
    {
        // make this atomic
        // ----------------------------------------------------
        return $this->runInALock('paying-advisor-' . $this->model->id, function () {

            AdvisorRequestsPaymentMonitor::completeIntentPayment($this->model);

            return $this->fetch();

        });
    }

    /**
     * @param Request $request
     * @return string
     * @throws ValidationException
     * @throws \Exception
     */
    private function generateCardToken(Request $request): string
    {
        $this->validatedRules([
            'card_number' => 'required|numeric',
            'expiry_month' => 'required|numeric',
            'expiry_year' => 'required|numeric',
            'cvc' => 'filled|numeric',
        ]);

        try {

            $SDK = new StripeSubscriptionSDK();
            return $SDK->createCardToken(
                $request->input('card_number'), $request->input('expiry_month'),
                $request->input('expiry_year'), $request->input('cvc'),
            )->id;

        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry, your token could not be created! Please, make sure you entered the right details.", 0, $exception);
        }

    }
    /**
     * @param Request $request
     * @return string
     * @throws ValidationException
     * @throws \Exception
     */
    private function notifyCopilot()
    {

        try {

            $get_advisor_request = $this->model->advisor_assigned_copilot()->first();

            if($get_advisor_request)
            {
                $request_data =   AdvisorRequest::with( "advisor_request_status","advisor_assigned_copilot", "advisor_request_attachments","user" )->where('id',$get_advisor_request->advisor_request_id)->first();
                if( isset($request_data) && $request_data->request_type ==2)
                {
                    Mail::send( new NewHourlyRequestReceivedMail (  $this->model, $this->user  ) );
                }
                else{
                    // send email
                    Mail::send( new NewRequestReceivedMail ( $request_data ) );
                }
            }
            else
            {
                $this->notifyAllCopilot();
            }

        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry something wrong with sending email notification .", 0, $exception);
        }
    }


    /**
     * @param Request $request
     * @return string
     * @throws ValidationException
     * @throws \Exception
     */
    private function notifyAllCopilot()
    {
        // $request = $this->getDataQuery()->first();

        try {
                $request_data =   AdvisorRequest::with( "advisor_request_status","advisor_assigned_copilot", "advisor_request_attachments","user" )->where('id',$this->model->id)->first();
                $copilots = User::where( 'account_status_id' , AccountStatus::APPROVED )->get();

                $job = (new \App\Jobs\NewRequestReceivedForAllJob($request_data, $copilots));
                dispatch($job);

        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry something wrong with sending email notification .", 0, $exception);
        }
    }

    /**
        * This function will return the number of hours a advisor or agent saved using lucia.
        * - total the $$ they've spent on requests
        *   - divide the total spend by $15
        *  - if they've spent $60
        * - $60 divide by $15 = 4 hours
     * @return int
     * @throws ValidationException
     * @throws \Exception
     */
    public function savedHours()
    {
        try {

            $totalSpent = AdvisorRequest::where('advisor_request_status_id', 4)->where('created_by_id',$this->user->id)->sum('total_amount');
            $totalHours = round($totalSpent/15);
            // $totalHours = number_format($totalHours);
            return $totalHours ? $totalHours : null;

        } catch (\Exception $exception) {
            Log::error(print_r($exception->getMessage()));
            Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry something wrong with geting hours .", 0, $exception);
        }
    }

    public function assignNewCopilot( Request $request )
    {
        try {
            $copilot = null;
        if( $request->has('copilot_id') )
        {
            $copilot = User::getById($request->get('copilot_id'));
            if( !$copilot->isCopilot() ) throw new \Exception( "The user you selected is not a copilot!" );
        }

        $advisorRequest = AdvisorRequest::where('id', $request->get('advisor_request_id'))->first();
        if($advisorRequest && $copilot!=null){
            $advisorRequest->advisor_assigned_copilot()->update([
                'copilot_id' => $copilot->id
            ]);
        }
        } catch (\Exception $exception) {
            throw new \Exception("Sorry something wrong with sending email notification .", 0, $exception);
        }
    }

    public function updateStatusOpenToAll( Request $request )
    {
        try {
            $advisorRequest = AdvisorRequest::where('id', $request->get('advisor_request_id'))->first();
            if($advisorRequest){
                DB::table('advisor_assigned_copilot')
                ->where('advisor_request_id',$request->get('advisor_request_id'))
                ->delete();
            }
            $request_data =   AdvisorRequest::with( "advisor_request_status","advisor_assigned_copilot", "advisor_request_attachments" )->where('id',$request->get('advisor_request_id'))->first();
            if($request_data)
            {
                    $copilots = User::where( 'account_status_id' , AccountStatus::APPROVED )->get();
                    $job = (new \App\Jobs\NewRequestReceivedForAllJob($request_data, $copilots));
                    dispatch($job);
            }

        } catch (\Exception $exception) {
            throw new \Exception("Sorry something wrong with sending email notification .", 0, $exception);
        }
    }

    public function cancelAdvisorRequest( Request $request )
    {
        try {
            $advisorRequest = AdvisorRequest::where('id', $request->get('advisor_request_id'))->first();

            /*
                Previously we were using this concept , the the copilot will refund.  but now we have option for advisor to directly
                refund the request whenever user want.
            */
        //    if ($advisorRequest->advisor_request_status_id !== AdvisorRequestStatus::ACCEPTED)
        //    throw new \Exception('You can not create refund on this advisor request because it is not in ACCEPTED status anymore.');

            if($advisorRequest){
                $advisorRequest->refundCharge();
                \Log::info("Refunded request {$advisorRequest->id} has been refunded by {$this->user->first_name } {$this->user->last_name }");
                Mail::send( new NotifyAdminRequestRefunded (  $advisorRequest, $this->user  ) );
                // $advisorRequest->advisor_request_status_id = AdvisorRequestStatus::CANCELLED;
                $advisorRequest->save();
            }

        } catch (\Exception $exception) {
            // echo $exception->getMessage();
           throw new \Exception("Sorry something wrong wwhile cancelling the request please contact us.", 0, $exception);
        }
    }

    public function getMyRequest($status){

        return $this->getDataQuery()
        ->with( "advisor_request_status","advisor_request_attachments","user" )
        ->where("advisor_request.advisor_request_status_id",$status )
        ->where("advisor_request.created_by_id",$this->user->id)
        ->orderBy('id','desc')
        ->get()
        ->map( function(AdvisorRequest $advisorRequest) {
            return
                array_merge(
                    $advisorRequest->presentForDev(),
                    [
                        "created_at" => $this->getRequestPostedTime($advisorRequest->created_at->toIso8601String()),
                        "updated_at" => $advisorRequest->updated_at->toIso8601String(),
                    ]
                );
        });
    }

    public function getRequestPostedTime($date){

        $date2 = date('Y-m-d');
        $date1 = date('Y-m-d',strtotime($date));
        $datetime1 = date_create($date1);
        $datetime2 = date_create($date2);
        $interval = date_diff($datetime1, $datetime2);
        $PostedString ='Today';
        if($interval->y > 0){
            $PostedString = $interval->y.' Year(s)';
            $PostedString .= ($interval->m > 0)?' and '.$interval->m. ' Month(s)':'';
            $PostedString .=' Ago';
        }elseif($interval->m > 0){
            $PostedString = $interval->m.' month(s)';
            $PostedString .= ($interval->d > 0)?' and '.$interval->d. ' Day(s)':'';
            $PostedString .=' Ago';
        }elseif($interval->d > 0){
            $PostedString = $interval->d.' Day(s)';
            $PostedString .=' Ago';
        }else{
            $PostedString = 'Today';
        }

        return $PostedString;
    }

    public function getChatWiseRequest(Request $request){
        if(!empty($request->request_id)){
            return  $AdvisorChatRequests = AdvisorChat::with('advisor_request')->where('receiver_id',$this->user->id)->where('advisor_request_id',$request->request_id)->groupBy('advisor_request_id')->orderby('created_at','desc')->get();
        }else{
            return  $AdvisorChatRequests = AdvisorChat::with('advisor_request')->where('receiver_id',$this->user->id)->groupBy('advisor_request_id')->orderby('created_at','desc')->get();
        }

    }

    public function getCopilots(Request $request){

        $concierges =  User::select('users.*')->with(['copilot_info', 'isFavorite' => function ($query) use ($request) {
            $query->where('client_id', $this->user->id);
        }])
        ->leftJoin('copilot_average_feedback','users.id','=','copilot_average_feedback.copilot_id')
        ->where("account_status_id", AccountStatus::APPROVED)
        ->whereHas("roles", function (Builder $builder) {
            $builder->where("user_role.role_id", Role::Concierge);
        });

        if ($request->q) {
            $concierges->leftJoin('copilot_info', 'users.id', '=', 'copilot_info.copilot_id')
            ->where(function ($query) use ($request) {
                $query->whereRaw('LOWER(users.name) like ?', [strtolower($request->q) . '%'])
                ->orWhereRaw('LOWER(REGEXP_REPLACE(copilot_info.bio, \'<[^>]*>\', \'\')) LIKE ?', ['%' . strtolower($request->q) . '%'])
                ->orWhereRaw('copilot_info.bio LIKE ?', ['%' . $request->q . '%']);
            })
            ->orWhereHas('isFavorite', function ($query) use ($request) {
                $query->whereRaw('LOWER(users.name) like ?', [strtolower($request->q) . '%'])
                ->orWhereRaw('LOWER(REGEXP_REPLACE(copilot_info.bio, \'<[^>]*>\', \'\')) LIKE ?', ['%' . strtolower($request->q) . '%'])
                ->orWhereRaw('copilot_info.bio LIKE ?', ['%' . $request->q . '%']);
            });
        }

        if($request->sorting==="name_sorting_favorite"){
            $concierges->WhereHas("isFavorite", function (Builder $builder) {
                $builder->where("favorite_copilots.client_id", $this->user->id);
            });
        }
        if($request->sorting == 'Recent'):
            $concierges->orderby('users.created_at','asc'); //
        elseif($request->sorting == 'Rating_desc'):
            $concierges->orderby('average_rating','desc');
        elseif($request->sorting == 'Rating_asc'):
           $concierges->orderby('average_rating','asc');
        elseif($request->sorting == 'Rate_desc'):
            $concierges->orderby('hourly_rate','desc');
        elseif($request->sorting == 'Rate_asc'):
            $concierges->orderby('hourly_rate','asc');
        elseif($request->sorting == 'name_sorting_asc'):
            $concierges->orderby('name','asc');
        elseif($request->sorting == 'name_sorting_desc'):
            $concierges->orderby('name','desc');
        else:
            $concierges->orderby('users.created_at','asc');
        endif;

        $concierges = $concierges->get();

        $concierges->map(function ($user) {
            return [
                "feedback" => $user->rating, //call rating object
                "country" => $user->country->description
            ];
        });
        return $concierges;
    }
    public function markAsFavorite($copilot_id)
    {
        try {
            $makeFavorite =FavoriteCopilot::updateOrCreate(["copilot_id"=>$copilot_id, "client_id"=>$this->user->id]);
            // $makeFavorite->copilot_id = $copilot_id;
            // $makeFavorite->client_id  = $this->user->id;
            // $makeFavorite->save();
            return new OkResponse( ["message" =>"Added to favorites succesfully"] );
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
                    throw new \Exception("There is an error while marking the copilot as favorite", 0, $exception);
        }

    }
    public function markAsUnFavorite($id)
    {
        try {
            $makeFavorite = FavoriteCopilot::where('copilot_id',$id)->where('client_id',$this->user->id)->first();
            if($makeFavorite)
            {
                $makeFavorite->delete();
                return new OkResponse( ["message" =>"removed from favorites succesfully"] );
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
                    throw new \Exception("There is an error while marking the copilot as favorite", 0, $exception);
        }

    }
}
