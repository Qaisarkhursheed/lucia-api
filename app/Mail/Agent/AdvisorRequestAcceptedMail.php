<?php

namespace App\Mail\Agent;

use App\Mail\MailableBase;
use App\ModelsExtended\AdvisorRequest;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdvisorRequestAcceptedMail extends MailableBase implements ShouldQueue
{
    private AdvisorRequest $advisorRequest;

    /**
     * Create a new message instance.
     *
     * @param AdvisorRequest $advisorRequest
     */
    public function __construct(AdvisorRequest $advisorRequest, $copilot=null )
    {

        $this->advisorRequest = $advisorRequest;

        $this->subject =  "Request accepted by " . $copilot->first_name;
        $this->to( $advisorRequest->user->email );

    }

    /**
     * Build the message.
     *
     * @return AdvisorRequestAcceptedMail
     */
    public function build()
    {
        return $this->view( "mails.agent.advisor_request_accepted" )
            ->with( 'advisorRequest', $this->advisorRequest );
    }
}
