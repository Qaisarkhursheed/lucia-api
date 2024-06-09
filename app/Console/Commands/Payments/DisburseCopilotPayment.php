<?php

namespace App\Console\Commands\Payments;

use App\Mail\Agent\AdvisorRequestCompletedMail;
use App\Mail\Copilot\CopilotPaymentReceivedMail;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\AdvisorRequestStatus;
use App\Repositories\Stripe\StripeConnectSDK;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;

/**
 */
class  DisburseCopilotPayment  extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:disburse-copilot-payment {request_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will move the payment for the request to the copilot if completed and if the copilot is fully registered';

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
                self::disburse ( $request );
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
            ->where( 'advisor_request_status_id' , AdvisorRequestStatus::COMPLETED )
            ->whereHas( 'advisor_assigned_copilot', function (Builder $builder){
                $builder->where("advisor_assigned_copilot.is_paid", false);
            })
            ->when( $this->argument( 'request_id' ), function ( Builder $builder ){
                $builder->where( 'id' , $this->argument( 'request_id' ) );
            } )

            // auto disburse for the ones that are older than an hour
            ->when( !$this->argument( 'request_id' ), function ( Builder $builder ){
                $builder->whereDate( 'updated_at' , "<", Carbon::now()->addHours(-1));
            } )

            ->get();
    }

    /**
     * @param AdvisorRequest $advisorRequest
     * @return AdvisorRequest
     * @throws ApiErrorException
     */
    public static function disburse(AdvisorRequest $advisorRequest): AdvisorRequest
    {
        // make this atomic
        // ----------------------------------------------------
        if ($advisorRequest->advisor_request_status_id !== AdvisorRequestStatus::COMPLETED)
            throw new \Exception('You can not disburse payment on this advisor request because it is not marked completed!');

        if (
            !$advisorRequest->advisor_assigned_copilot->user->user_stripe_account
            || !$advisorRequest->advisor_assigned_copilot->user->user_stripe_account->connect_boarding_completed
        )
            throw new \Exception('Copilot does not have a fully connected account yet!');

        // ignore this for old requests
        if( $advisorRequest->sub_amount > 0 && $advisorRequest->advisor_request_payment->stripe_charge_id )
        {
            $SDK = new StripeConnectSDK();

//            || $advisorRequest->advisor_request_payment->created_at->diffInDays(Carbon::now()) > 1 // don't use charge if this is older
            $TaskAmount = $advisorRequest->sub_amount;
// Substruct Lucia charges from each of the request will be 20%

            $LuciaAmount = $TaskAmount*(env("LUCIA_DEDUCTION_IN_PERCENTAGE", 20)/100);

            $TaskAmount = $TaskAmount - $LuciaAmount;
            $LuciaAmount = $LuciaAmount + $advisorRequest->fee;

            try {
                $stripe_charge_id = $advisorRequest->discount > 0 ? null : $advisorRequest->advisor_request_payment->stripe_charge_id;
                $SDK->createTransfer(
                    $TaskAmount,
                    $advisorRequest->advisor_assigned_copilot->user->user_stripe_account->getStripeConnectIdAttribute(),

                    // transfer from our account if there is discount, if not transfer from the charge itself
                    // if not you will get transfer exceeds error. [Transfers using this transaction as a source must not exceed the source amount of $93.00]
                    $stripe_charge_id,

                    $advisorRequest->advisor_request_payment->stripe_payment_intent->getStripeTransferGroupAttribute(),
                );

                //Lucia Amount dedection
                self::createIntentPaymentForLucia($LuciaAmount,$stripe_charge_id,$advisorRequest->advisor_request_payment->stripe_payment_intent->getStripeTransferGroupAttribute());
            }catch (\Exception $exception){

                Log::error($exception->getMessage());

                // try direct charges
                if( Str::of($exception->getMessage())->contains("You have insufficient funds") )
                {
                    $SDK->createTransfer(
                        $TaskAmount,
                        $advisorRequest->advisor_assigned_copilot->user->user_stripe_account->getStripeConnectIdAttribute(),

                        // transfer from our account if there is discount, if not transfer from the charge itself
                        // if not you will get transfer exceeds error. [Transfers using this transaction as a source must not exceed the source amount of $93.00]
                        null,

                        $advisorRequest->advisor_request_payment->stripe_payment_intent->getStripeTransferGroupAttribute(),
                    );
                    //Lucia Amount dedection
                    self::createIntentPaymentForLucia($LuciaAmount,null,$advisorRequest->advisor_request_payment->stripe_payment_intent->getStripeTransferGroupAttribute());

                }
                else{
                    Log::error($exception->getMessage());
                    throw $exception;
                }
            }


        }

        $advisorRequest->advisor_assigned_copilot->update([
            'is_paid' =>true,
        ]);

        // don't notify if no real money was sent
        if( $advisorRequest->sub_amount > 0 && $advisorRequest->advisor_request_payment->stripe_charge_id )
        {
            // send notification of payment
            Mail::send( new CopilotPaymentReceivedMail( $advisorRequest ) );
        }

        return $advisorRequest;
    }


    public static function createIntentPaymentForLucia($amount,$stripe_charge_id=null,$AdvisorStripePaymentIntent){
        $SDK = new StripeConnectSDK();
        // $this->info( "Trying to create amount for lucia". $amount);
        // $this->info( "with STRIPE_LUCIA_PRODUCT_ID ". $env('STRIPE_LUCIA_PRODUCT_ID'));

        $SDK->createTransfer($amount,env('STRIPE_LUCIA_PRODUCT_ID'),$stripe_charge_id,$AdvisorStripePaymentIntent);
    }
}
