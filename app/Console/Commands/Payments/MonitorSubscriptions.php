<?php

namespace App\Console\Commands\Payments;

use App\ModelsExtended\Role;
use App\ModelsExtended\StripeSubscriptionHistory;
use App\ModelsExtended\UserRole;
use App\Repositories\Stripe\StripeSubscriptionSDK;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription;

/**
 * This doesn't detect if user has subscriptions on stripe,
 * only monitor user that has subscription on lucia
 */
class  MonitorSubscriptions  extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:monitor-subscriptions {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will monitor changes to subscription for customer accounts with on going subscriptions';

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
        $this->withProgressBar( $this->getUserRoles(), function ( UserRole $userRole ){

            $this->monitorSubscription( $userRole );

        });

        $this->info( "\n-------------------------------------" );
        $this->info( "COMPLETED" );

        return true;
    }

    /**
     * Only select users with active subscriptions on record
     *
     * @return array|Builder[]|Collection
     */
    private function getUserRoles()
    {
        return UserRole::with('user')
            ->where("user_role.role_id", Role::Agent)
            ->whereNotNull( 'user_role.stripe_subscription' )
            ->when( $this->argument( 'user_id' ), function ( Builder $builder ){
                $builder->whereHas("user", function (Builder $builder) {
                    $builder->where( 'users.id' , $this->argument( 'user_id' ) );
                });
            } )
            ->get();
    }

    /**
     * @param UserRole $userRole
     * @return void
     */
    private function monitorSubscription(UserRole $userRole)
    {
        // if user has active subscription and current period is not over, there is no need to call stripe
        if( $userRole->has_valid_license
            && $userRole->getStripeSubscriptionEndDateAttribute()->greaterThanOrEqualTo( Carbon::now() ) )
            return;


        // fetch current subscription
        // if status == active and not has license
        //      add license and log history
        //
        // else status == cancelled
        //      do nothing until the period has expired
        //      if period has expired
        //          set has license to false
        //          log subscription history
        //          clear subscription from local database
        //
        //  else status == incomplete
        //      means still owing some invoices
        //      if user has license then
        //          switch to no license
        //          update subscription and log as well
        //
        //  else throw exception (Unknown Yet) - Study
        //

        $sdk = new StripeSubscriptionSDK();
        try {

            $subscription = $sdk->fetchSubscription( $userRole->getStripeSubscriptionIdAttribute() );

            // https://stripe.com/docs/api/subscriptions/object
            if(
                DetectSubscriptions::consideredAsActive( $subscription->status )
                &&
                ! $this->userNeedTreatUnpaidInvoices( $userRole, $sdk )
            )
                $this->treatActiveSubscription( $userRole, $subscription );

            if( $subscription->status === "past_due" || $subscription->status === "unpaid"  )
                $this->treatPastDueSubscription( $userRole, $subscription );

            if( $subscription->status === "canceled"   )
                $this->treatCancelledSubscription( $userRole, $subscription );

        }catch (Exception $exception ){
            $this->error( "Error - " . __FUNCTION__ );
            Log::error( $exception->getMessage() , $exception->getTrace() );
        }
    }


    /**
     * checks if we just treated unpaid invoices as not allowed.
     *
     * @param UserRole $userRole
     * @param StripeSubscriptionSDK $sdk
     * @return bool
     * @throws ApiErrorException
     */
    private function userNeedTreatUnpaidInvoices(UserRole $userRole, StripeSubscriptionSDK $sdk): bool
    {
        // if you still have pending invoice, you have to pay it. lol

        if(
            count( $sdk->listPendingInvoices( $userRole->user->user_stripe_account->getStripeCustomerIdAttribute() ) )
            ||
            count( $sdk->listUncollectibleInvoices( $userRole->user->user_stripe_account->getStripeCustomerIdAttribute() ) )
        )
        {
            $userRole->has_valid_license = false;
            $userRole->updateQuietly();
            return true;
        }
        return false;
    }

    /**
     * @param UserRole $userRole
     * @param Subscription $subscription
     * @return void
     */
    private function treatActiveSubscription(UserRole $userRole, Subscription $subscription)
    {
        try {

            // if status == active and not has license
            //      add license and log history
            if( ! $userRole->has_valid_license )
            {
                $userRole->has_valid_license = true;
                $userRole->stripe_subscription =  $subscription;
                $userRole->update();
            }
            StripeSubscriptionHistory::pushToHistory( $userRole );

        }catch (Exception $exception ){
            $this->error( "Error - " . __FUNCTION__ );
            Log::error( $exception->getMessage() , $exception->getTrace() );
        }
    }

    /**
     * if this is called it means the period is over
     *
     * @param UserRole $userRole
     * @param Subscription $subscription
     * @return void
     */
    private function treatCancelledSubscription(UserRole $userRole, Subscription $subscription )
    {
        try {

            // else status == cancelled
            //      do nothing until the period has expired
            //      if period has expired
            //          set has license to false
            //          log subscription history
            //          clear subscription from local database
            if( $userRole->has_valid_license )
            {
                // delete subscription from record
                $userRole->has_valid_license = false;
                $userRole->stripe_subscription = null;
                $userRole->updateQuietly();
            }

            // Check if we have not pushed to history before
            if( $userRole->hasActiveStripeSubscriptionAttribute() )
                StripeSubscriptionHistory::pushToDirectHistory( $userRole, $subscription );

        }catch (Exception $exception ){
            $this->error( "Error - " . __FUNCTION__ );
            Log::error( $exception->getMessage() , $exception->getTrace() );
        }
    }

    /**
     * If this calls it means time is overdue
     *
     * @param UserRole $userRole
     * @param Subscription $subscription
     * @return void
     */
    private function treatPastDueSubscription(UserRole $userRole, Subscription $subscription)
    {
        try {

            //  else status == incomplete
            //      means still owing some invoices
            //      if user has license then
            //          switch to no license
            //          update subscription and log as well
            //
            if( $userRole->has_valid_license )
            {
                $userRole->has_valid_license = false;
                $userRole->updateQuietly();

                StripeSubscriptionHistory::pushToDirectHistory( $userRole, $subscription );
            }

        }catch (Exception $exception ){
            $this->error( "Error - " . __FUNCTION__ );
            Log::error( $exception->getMessage() , $exception->getTrace() );
        }
    }
}
