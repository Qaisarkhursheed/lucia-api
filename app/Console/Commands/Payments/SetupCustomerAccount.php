<?php

namespace App\Console\Commands\Payments;

use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use App\Repositories\Stripe\StripeSubscriptionSDK;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;

class  SetupCustomerAccount  extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:setup-customer {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will setup customer account on stripe payments';

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
        $this->withProgressBar( $this->getUsers(), function ( User $user ){

            self::createOrUpdateCustomer( $user );

        });

        $this->info( "\n-------------------------------------" );
        $this->info( "COMPLETED" );

        return true;
    }


    /**
     * @return array|Builder[]|Collection
     */
    private function getUsers()
    {
        return User::query()
            // because at the point of creation no role is attached
//            ->whereHas("roles", function (Builder $builder) {
//                $builder->whereIn("user_role.role_id", [ Role::Agent , Role::Concierge ] );
//            })
            ->when( $this->argument( 'user_id' ), function ( Builder $builder ){
                $builder->where( 'id' , $this->argument( 'user_id' ) );
            } )
            ->get();
    }

    /**
     * @param User $user
     * @return void
     */
    public static function createOrUpdateCustomer(User $user)
    {
        $sdk = new StripeSubscriptionSDK();
        try {

            if( $user->user_stripe_account &&  $user->user_stripe_account->stripe_customer  )
            {
                self::updateCustomer($sdk, $user, $user->user_stripe_account->getStripeCustomerIdAttribute() );
            }else if( $customer = $sdk->getCustomerByEmail( $user->email ) ) {
                self::updateCustomer($sdk, $user, $customer->id );
            } else {
                $customer = $sdk->createCustomer( $user->name, $user->email, $user->agency_name, $user->phone );
                self::updateCustomer($sdk, $user,$customer->id, $customer);
            }
        }catch (\Exception $exception ){
//            $this->error( "Error Updating/Creating Customer" );
            Log::error( $exception->getMessage() , $exception->getTrace() );
        }
    }

    /**
     * This will call sdk to update and then update/create local db
     * @param StripeSubscriptionSDK $sdk
     * @param User $user
     * @param string $stripe_customer_id
     * @return void
     * @throws ApiErrorException
     */
    public static function updateCustomer(StripeSubscriptionSDK $sdk,
                                    User $user,
                                    string $stripe_customer_id,
                                    ?Customer $customer = null
    ): void
    {
        if( ! $customer )
            $customer = $sdk->updateCustomer(
            $stripe_customer_id,
            $user->name, $user->email, $user->agency_name, $user->phone
        );

        // I assume here that it won't be deleted if it was created
        $user->user_stripe_account()->updateOrInsert(
            [ 'user_id' => $user->id ],
            [ 'stripe_customer' => json_encode($customer->toArray() ) ]
        );

//
//        $user->user_stripe_account->stripe_customer =
//            $sdk->updateCustomer(
//                $stripe_customer_id,
//                $user->name, $user->email, $user->agency_name, $user->phone
//            )->toArray();
//        $user->user_stripe_account->updateQuietly();
    }
}
