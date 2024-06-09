<?php

namespace App\Console\Commands\Payments;

use App\ModelsExtended\Role;
use App\ModelsExtended\StripeSubscriptionHistory;
use App\ModelsExtended\User;
use App\ModelsExtended\UserRole;
use App\Repositories\Stripe\StripeSubscriptionSDK;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription;

/**
 * This detects if user has subscriptions on stripe and no subscription on lucia
 */
class  DetectSubscriptions  extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:detect-subscriptions {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This detects if user has subscriptions on stripe and no subscription on lucia';

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
     * @throws ApiErrorException
     */
    public function handle()
    {
        // fetch all products
        $this->withProgressBar( $this->getUsers(), function ( User $user ){

            $this->detectIfCustomerAlreadyHasActiveSubscription( $user );

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
    private function getUsers()
    {
        return User::with('user_stripe_account')
            ->whereHas("roles", function (Builder $builder) {
                $builder->where("user_role.role_id", Role::Agent)
                    ->where( 'user_role.has_valid_license' , false )
                    ->whereNull( 'user_role.stripe_subscription' );
            })
            ->whereHas( 'user_stripe_account' ,function ( Builder $builder ){
                $builder->whereNotNull( 'user_stripe_account.stripe_customer' );
            } )
            ->when( $this->argument( 'user_id' ), function ( Builder $builder ){
                $builder->where( 'id' , $this->argument( 'user_id' ) );
            } )
            ->get();
    }

    /**
     * Explains status we allow as active
     *
     * @param string $status
     * @return bool
     */
    public static function consideredAsActive( string $status ): bool
    {
        return collect([ 'active' , 'trialing' ])->contains( $status );
    }

    /**
     * @param User $user
     * @return void
     * @throws ApiErrorException
     */
    public static  function detectIfCustomerAlreadyHasActiveSubscription(User $user)
    {
        $sdk = new StripeSubscriptionSDK();
        $actives = collect( $sdk->fetchCustomerSubscriptions( $user->user_stripe_account->getStripeCustomerIdAttribute() ) )
            ->filter( function ( Subscription $subscription) {
                return self::consideredAsActive( $subscription->status );
            });
        foreach ($actives as $active)
           self::addActiveSubscriptionToUser( $user, $active );
    }

    /**
     * @param User $user
     * @param Subscription $subscription
     * @return void
     */
    public static function addActiveSubscriptionToUser(User $user, Subscription $subscription)
    {
        $role_id = $subscription->metadata && array_key_exists( "role_id", $subscription->metadata->toArray() )? intval($subscription->metadata->toArray()["role_id"]) : Role::Agent;
        $userRole = UserRole::getUserRole( $role_id , $user->id);
        $userRole->stripe_subscription =  $subscription;
        $userRole->has_valid_license = true;
        $userRole->updateQuietly();

        StripeSubscriptionHistory::pushToHistory( $userRole);
    }
}
