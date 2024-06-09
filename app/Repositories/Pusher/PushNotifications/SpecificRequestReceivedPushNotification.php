<?php

namespace App\Repositories\Pusher\PushNotifications;

use App\Jobs\Job;
use App\ModelsExtended\AdvisorRequest;
use App\Repositories\Pusher\PusherBeam;
use Illuminate\Support\Facades\Log;

/**
 * This notification is for specific copilot selected during request creation
 */
class SpecificRequestReceivedPushNotification  extends Job
{
    private AdvisorRequest $advisorRequest;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AdvisorRequest $advisorRequest)
    {
        $this->advisorRequest = $advisorRequest;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PusherBeam $beam)
    {
        try {

            $beam->publishToUsers([ strval($this->advisorRequest->advisor_assigned_copilot->copilot_id) ],
                "Request | " .  $this->advisorRequest->request_title,
                "New Request Received from " .  $this->advisorRequest->user->first_name . ". Visit LUCIA to accept it.",
            );

        }catch (\Exception $exception){
            Log::error( $exception->getMessage() );
        }
    }

}
