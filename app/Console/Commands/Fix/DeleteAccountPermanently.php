<?php

namespace App\Console\Commands\Fix;

use App\ModelsExtended\User;
use App\Repositories\Stripe\StripeConnectSDK;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DeleteAccountPermanently  extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:delete-user-permanently {user_ids : Separated comma list of user ids to delete }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will permanently delete specified user accounts';

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
        if (!$this->confirm('Do you wish to continue?')) return false;

        $users = $this->getUsers();

        if( $users->count() )
            if (!$this->confirm(
                sprintf("Are you sure you want to delete these users: \n\n%s\n",
                $users->map(fn( User $user ) => sprintf( "  -> %s | %s (%s)", $user->id, $user->name, $user->email  ) )
                    ->implode("\n")
            ))) return false;

        $this->withProgressBar( $users, function ( User $user ){

            try {
                self::deleteUser( $user );
                $this->info( "\n" . $user->email . " DELETED!\n" );
            }catch (\Exception $exception){
                $this->error( "\n" . $user->email . " FAILED!\n" );
                $this->error( $exception->getMessage() . "\n" );
            }

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
        $users = Str::of( $this->argument( 'user_ids' ) )->explode(",")
            ->map(fn(string $x) => trim($x) );

        return User::withTrashed()
                ->whereIn( 'id' , $users->toArray() )
            ->get();
    }

    public static function deleteUser(User $user)
    {
        DB::transaction(function () use ($user){

            $user->service_suppliers()->delete();
            $user->saved_suppliers()->delete();
            $user->master_sub_account()->delete();
            $user->stripe_audit_logs()->delete();
            $user->stripe_checkout_logs()->delete();
            $user->stripe_subscription_histories()->delete();
            $user->stripe_connect_reminders()->delete();

            $user->advisor_requests()->delete();
            $user->itineraries()->forceDelete(); // using soft delete

            $user->stripe_payment_intents()->delete();

            self::deleteStripeAccounts($user);

            $user->roles()->delete();
           $user->user_stripe_account()->delete();

           $user->booking_ocrs()->delete();

            Storage::cloud()->delete($user->getFolderStorageRelativePath());
            $user->forceDelete(); // using soft delete


        });
    }

    /**
     * @param User $user
     * @return void
     */
    private static function deleteStripeAccounts(User $user): void
    {
        try {
            $SDK = new StripeConnectSDK();
            if ($user->user_stripe_account && $user->user_stripe_account->getStripeSubscriptionIdAttribute())      // cancel subscription
                $SDK->cancelSubscription($user->user_stripe_account->getStripeSubscriptionIdAttribute());

            if ($user->user_stripe_account && $user->user_stripe_account->getStripeConnectIdAttribute())
                $SDK->deleteConnectAccount($user->user_stripe_account->getStripeConnectIdAttribute());

            if ($user->user_stripe_account && $user->user_stripe_account->getStripeCustomerIdAttribute())
                $SDK->deleteCustomer($user->user_stripe_account->getStripeCustomerIdAttribute());
        }catch (Exception $exception){
            // ignore
            Log::error($exception->getMessage(), $exception->getTrace());
        }
    }
}
