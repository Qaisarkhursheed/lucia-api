<?php

namespace App\Console\Commands\Payments;

use App\Mail\Copilot\NewRequestReceivedMail;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\AdvisorRequestStatus;
use App\Repositories\Pusher\PushNotifications\SpecificRequestReceivedPushNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\ApiErrorException;

/**
 * This checks if the intent is completed and finishes the process
 */
class  AdvisorRequestsPaymentMonitor  extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:advisor-payment-intent {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will re-confirm payment intents stuck for some advisor requests';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // fetch all products
        $this->withProgressBar( $this->getAdvisorRequests(), function ( AdvisorRequest $request ){

            try {
                self::completeIntentPayment ( $request );
                $this->info( sprintf( "\n %s: [ %s ] => DONE! \n", $request->id, $request->request_title) );
            }catch (\Exception $exception){
                $this->info( sprintf( "\n %s: [ %s ] => %s \n", $request->id, $request->request_title, $exception->getMessage() ) );
            }
        });

        $this->info( "\n-------------------------------------" );
        $this->info( "COMPLETED" );

        return true;
    }


    /**
     * @return array|Builder[]|Collection
     */
    private function getAdvisorRequests()
    {
        return AdvisorRequest::query()
            ->where( 'advisor_request_status_id' , AdvisorRequestStatus::DRAFT )
            ->whereNotNull( 'stripe_payment_intent_id')
            ->when( $this->argument( 'user_id' ), function ( Builder $builder ){
                $builder->where( 'created_by_id' , $this->argument( 'user_id' ) );
            } )
            ->get();
    }

    /**
     * @param AdvisorRequest $advisorRequest
     * @return AdvisorRequest
     * @throws ApiErrorException
     * @throws \Exception
     */
    public static function completeIntentPayment(AdvisorRequest $advisorRequest)
    {
        // make this atomic
        // ----------------------------------------------------
        if ($advisorRequest->advisor_request_status_id !== AdvisorRequestStatus::DRAFT)
            throw new \Exception('You can not make payment on this advisor request because it is not in draft status anymore.');

        if ( !$advisorRequest->advisor_request_payment->stripe_payment_intent )
            throw new \Exception('You have not created any payment intent on this advisor request.');

        $advisorRequest->advisor_request_payment->stripe_payment_intent->retrieveUpdatedData();

        if( $advisorRequest->advisor_request_payment->stripe_payment_intent->succeeded )
        {
            $advisorRequest->update([
                'advisor_request_status_id' => AdvisorRequestStatus::PAID,
            ]);

            $advisorRequest->advisor_request_payment->update([
                'stripe_charge_id' => $advisorRequest->advisor_request_payment->stripe_payment_intent->getStripeChargeIdAttribute(),
            ]);
            if($advisorRequest->request_type ==2 && $advisorRequest->advisor_request_status_id == AdvisorRequestStatus::PAID)
            {
                sendAlertMessageToSlack("Hey, {$advisorRequest->user->first_name} {$advisorRequest->user->last_name} posted new hourly request on lucia. (Paid)");
            }
            return $advisorRequest;

        } throw new \Exception( "Payment intent is still not completed on stripe! against request id: ".$advisorRequest->id );
    }


    public static function fireEventsOnPayment(AdvisorRequest $advisorRequest): AdvisorRequest
    {
        // notify if assigned
        $advisorRequest->refresh();
        if( $advisorRequest->advisor_assigned_copilot ) {
            Mail::send( new NewRequestReceivedMail( $advisorRequest ));
            dispatch( new SpecificRequestReceivedPushNotification( $advisorRequest ) );
        }else{
            $advisorRequestId = $advisorRequest->id;
            dispatch( function () use ( $advisorRequestId ){
                Artisan::call('notify:new-request-available', [
                    "advisor_request_id" => $advisorRequestId
                ]);
            });
        }
        return $advisorRequest;
    }

}
