<?php

namespace App\Mail\Copilot;

use App\Mail\MailableBase;
use App\ModelsExtended\AdvisorRequest;
use Illuminate\Contracts\Queue\ShouldQueue;

class RequestSentBackToPoolMail extends MailableBase implements ShouldQueue
{
    private AdvisorRequest  $AdvisorRequest ;
    private string $senderName;

    /**
     * Create a new message instance.
     *
     * @param AdvisorRequest $AdvisorRequest
     * @param string $senderName
     */
    public function __construct(AdvisorRequest  $AdvisorRequest, string $senderName )
    {
        $this->AdvisorRequest  = $AdvisorRequest ;
        $this->senderName = $senderName;

        $this->subject =   $this->ucwordsSubject( "Request Refunded by "  .  $senderName  );
        $this->to( $this->AdvisorRequest->user->email );

    }

    /**
     * Build the message.
     *
     * @return RequestSentBackToPoolMail
     */
    public function build()
    {
        return $this->view( "mails.copilot.request_sent_back_to_pool" )
            ->with( 'senderName', $this->senderName  )
            ->with( 'advisorRequest', $this->AdvisorRequest  );
    }
}
