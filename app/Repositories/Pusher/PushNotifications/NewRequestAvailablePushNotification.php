<?php

namespace App\Repositories\Pusher\PushNotifications;

use App\Jobs\Job;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\User;
use App\Repositories\Pusher\PusherBeam;
use Illuminate\Support\Facades\Log;

/**
 * This notification is for all active copilot about new request posted
 */
class NewRequestAvailablePushNotification  extends Job
{
    private AdvisorRequest $advisorRequest;
    private User $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AdvisorRequest $advisorRequest, User $user)
    {
        $this->advisorRequest = $advisorRequest;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PusherBeam $beam)
    {
        try {
//            Log::info("This was called to " . $this->user->id );
//            Log::info("This was called by " . $this->advisorRequest->id );
            $beam->publishToUsers([ strval($this->user->id) ],
                "Request | " .  $this->advisorRequest->request_title,
                "New Request Available from " .  $this->advisorRequest->user->first_name . ". Visit LUCIA to accept it.",
            );

        }catch (\Exception $exception){
            Log::error( $exception->getMessage() );
        }
    }

}
