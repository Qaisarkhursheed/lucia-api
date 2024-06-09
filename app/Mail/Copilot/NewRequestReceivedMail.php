<?php

namespace App\Mail\Copilot;

use App\Mail\MailableBase;
use App\ModelsExtended\AdvisorRequest;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewRequestReceivedMail extends MailableBase implements ShouldQueue
{
    private AdvisorRequest  $AdvisorRequest ;

    /**
     * Create a new message instance.
     *
     * @param AdvisorRequest  $AdvisorRequest
     */
    public function __construct(AdvisorRequest  $AdvisorRequest  )
    {
        $this->AdvisorRequest  = $AdvisorRequest ;

        $this->subject =   $this->ucwordsSubject( "New request from "  .  $this->AdvisorRequest->user->first_name  );
        $this->to( $this->AdvisorRequest->advisor_assigned_copilot->user->email );

    }

    /**
     * Build the message.
     *
     * @return NewRequestReceivedMail
     */
    public function build()
    {
        return $this->view( "mails.copilot.new_request_received" )
            ->with( 'advisorRequest', $this->AdvisorRequest  );
    }
}
