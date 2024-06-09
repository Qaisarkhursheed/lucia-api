<?php

namespace App\Mail\Copilot;

use App\Mail\MJMLMailable;
use App\ModelsExtended\AdvisorRequest;
use Illuminate\Contracts\Queue\ShouldQueue;

class CopilotPaymentReceivedMail extends MJMLMailable implements ShouldQueue
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

        $this->subject =   $this->ucwordsSubject( "Payment Received | "  .  $this->AdvisorRequest->request_title  );
        $this->to( $this->AdvisorRequest->advisor_assigned_copilot->user->email );

    }

    /**
     * Build the message.
     *
     * @return MJMLMailable
     */
    public function build()
    {
        return $this->mjmlBlade( "copilot_payment_received" )
            ->with( 'advisorRequest', $this->AdvisorRequest  );
    }
}
