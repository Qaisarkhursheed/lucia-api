<?php

namespace App\Repositories\Pusher\PushNotifications;

use App\Jobs\Job;
use App\ModelsExtended\AdvisorRequest;
use App\Repositories\Pusher\PusherBeam;
use Illuminate\Support\Facades\Log;

class AdvisorRequestAcceptedPushNotification  extends Job
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

            $beam->publishToUsers([ strval($this->advisorRequest->created_by_id) ],
                "Request | " .  $this->advisorRequest->request_title,
                "Request Accepted by " .  $this->advisorRequest->advisor_assigned_copilot->user->first_name,
            );

        }catch (\Exception $exception){
            Log::error( $exception->getMessage() );
        }
    }

}
