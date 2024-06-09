<?php

namespace App\Mail\Agent;

use App\Mail\MJMLMailable;
use App\ModelsExtended\AdvisorRequest ;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdvisorRequestCompletedMail extends MJMLMailable implements ShouldQueue
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

        $this->subject =   $this->ucwordsSubject( "Request Completed | "  .  $this->AdvisorRequest->request_title  );
        $this->to( $this->AdvisorRequest->user->email );

    }

    /**
     * Build the message.
     *
     * @return MJMLMailable
     */
    public function build()
    {
        return $this->mjmlBlade( "advisor_request_completed" )
            ->with( 'AdvisorRequest', $this->AdvisorRequest  );
    }
}
