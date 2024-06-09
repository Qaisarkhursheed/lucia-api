<?php

namespace App\Console\Commands\Payments;

use App\Mail\Copilot\StripeAccountConnectedMail;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use App\Repositories\Stripe\StripeConnectSDK;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class  MonitorConnectAccounts  extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:monitor-connect-account {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will monitor changes to connect account for customer';

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

            $this->monitorAccount( $user );

        });

        $this->info( "\n-------------------------------------" );
        $this->info( "COMPLETED" );

        return true;
    }

    /**
     * Only select users with active connect account and not completed
     *
     * @return array|Builder[]|Collection
     */
    private function getUsers()
    {
        return User::with('user_stripe_account')
            ->whereHas("roles", function (Builder $builder) {
                $builder->where("user_role.role_id", Role::Concierge);
            })
            ->whereHas( 'user_stripe_account' ,function ( Builder $builder ){
                $builder->whereNotNull( 'user_stripe_account.stripe_connect_account' )

                    // I can remove this later if I need to monitor all accounts
                    // because it can switch from completed to false again
                    ->where( function ( Builder $builder ){
                        $builder->where( 'user_stripe_account.connect_boarding_completed', false )
                            ->orWhereNull( 'user_stripe_account.connect_boarding_completed');
                    });
            } )
            ->when( $this->argument( 'user_id' ), function ( Builder $builder ){
                $builder->where( 'id' , $this->argument( 'user_id' ) );
            })
            ->get();
    }

    /**
     * @param User $user
     * @return void
     */
    private function monitorAccount(User $user)
    {
        $sdk = new StripeConnectSDK();
        try {

            $account = $sdk->retrieveConnectAccount( $user->user_stripe_account->getStripeConnectIdAttribute() );

            $user->user_stripe_account->stripe_connect_account = $account->toArray();
            $user->user_stripe_account->connect_boarding_completed = (
                $account->charges_enabled &&
                $account->toArray()['capabilities']['transfers'] === "active"
            );

            $user->user_stripe_account->updateQuietly();

            // send notification connection
            Mail::send( new StripeAccountConnectedMail( $user ) );

        }catch (\Exception $exception ){
            $this->error( "Error - " . __FUNCTION__ );
            Log::error( $exception->getMessage() , $exception->getTrace() );
        }
    }
}
