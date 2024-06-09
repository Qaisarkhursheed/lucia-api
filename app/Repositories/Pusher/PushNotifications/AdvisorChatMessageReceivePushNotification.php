<?php

namespace App\Repositories\Pusher\PushNotifications;

use App\Jobs\Job;
use App\ModelsExtended\AdvisorChat;
use App\ModelsExtended\ChatContentType;
use App\Repositories\Pusher\PusherBeam;
use Illuminate\Support\Facades\Log;

class AdvisorChatMessageReceivePushNotification  extends Job
{
    private AdvisorChat $advisorChat;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AdvisorChat $advisorChat)
    {
        $this->advisorChat = $advisorChat;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PusherBeam $beam)
    {
        try {

            $beam->publishToUsers([ strval($this->advisorChat->receiver_id) ],
                "LUCIA | " .  $this->advisorChat->advisor_request->request_title,
                $this->createMessage()
            );

        }catch (\Exception $exception){
            Log::error( $exception->getMessage() );
        }
    }

    /**
     * @return string
     */
    private function createMessage()
    {
        if($this->advisorChat->chat_content_type_id === ChatContentType::DOCUMENT )
            return "File received: " . $this->advisorChat->plain_text;

        return  $this->advisorChat->plain_text;
    }
}
