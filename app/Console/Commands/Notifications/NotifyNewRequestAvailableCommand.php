<?php

namespace App\Console\Commands\Notifications;

use App\Mail\Copilot\NewHireRequestAvailableMail;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\AdvisorRequestStatus;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use App\ModelsExtended\AccountStatus;
use App\Repositories\Pusher\PushNotifications\NewRequestAvailablePushNotification;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

class  NotifyNewRequestAvailableCommand  extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:new-request-available {advisor_request_id?} {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This send all active copilots notification about the new request';


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
        $this->withProgressBar( $this->getAdvisorRequests(), function ( AdvisorRequest $advisorRequest ){
            $this->withProgressBar( $this->getUsers(), function ( User $user ) use( $advisorRequest) {
                try {

                    Mail::send( new NewHireRequestAvailableMail( $advisorRequest, $user ));
                    dispatch( new NewRequestAvailablePushNotification( $advisorRequest, $user ) );

                }catch (Exception $exception){
                    $this->error( $exception->getMessage() );
                }
            });
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
            ->where( 'advisor_request_status_id' , AdvisorRequestStatus::PAID )
            ->whereDoesntHave( 'advisor_assigned_copilot')
            ->when( $this->argument( 'advisor_request_id' ), function ( Builder $builder ){
                $builder->where( 'id' , $this->argument( 'advisor_request_id' ) );
            })
            ->get();
    }

    /**
     * select users
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
            ->when( $this->argument( 'user_id' ), function ( Builder $builder ){
                $builder->where( 'id' , $this->argument( 'user_id' ) );
            })
            ->get();
    }
}
