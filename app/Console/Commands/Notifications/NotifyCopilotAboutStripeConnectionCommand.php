<?php

namespace App\Console\Commands\Notifications;

use App\Mail\Copilot\StripeConnectionReminderMail;
use App\ModelsExtended\AccountStatus;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class  NotifyCopilotAboutStripeConnectionCommand  extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:notify-copilot-stripe {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will notify all copilots that has not successfully connected to stripe to connect';


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
     * @return bool
     */
    public function handle()
    {
        $this->withProgressBar( $this->getUsers(), function ( User $user ){

            try {

                $this->processUser($user);

            }catch (Exception $exception){
                $this->error( $exception->getMessage() );
            }
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
            ->where("account_status_id", AccountStatus::APPROVED)
            ->whereHas("roles", function (Builder $builder) {
                $builder->where("user_role.role_id", Role::Concierge);
            })
            ->whereHas( 'user_stripe_account' ,function ( Builder $builder ){
                $builder->whereNull( 'user_stripe_account.stripe_connect_account' )
                ->orWhere(function (Builder $builder){
                    $builder->whereNotNull( 'user_stripe_account.stripe_connect_account' )

                    // I can remove this later if I need to monitor all accounts
                    // because it can switch from completed to false again
                    ->where( function ( Builder $builder ){
                        $builder->where( 'user_stripe_account.connect_boarding_completed', false )
                            ->orWhereNull( 'user_stripe_account.connect_boarding_completed');
                    });
                });
            } )
            ->when( $this->argument( 'user_id' ), function ( Builder $builder ){
                $builder->where( 'id' , $this->argument( 'user_id' ) );
            })
            ->get();
    }

    private function processUser(User $user)
    {
        // check if we can send to user
        // -Email to remind them about connecting stripe account after 72 hours of creating the account
        // - repeats max 5 times

        // new user with no reminder
        if( !$user->last_stripe_connect_reminder
            && $user->created_at->diffInHours(Carbon::now()) < 72  ) return;

        // old user
        if( $user->last_stripe_connect_reminder &&
            $user->last_stripe_connect_reminder->created_at->diffInHours(Carbon::now()) < 72) return;

        // old user, max of 5
        if( $user->stripe_connect_reminders->count() >= 5) return;


        Mail::send( new StripeConnectionReminderMail( $user ) );

        // log
        $user->stripe_connect_reminders()->create();
    }
}
