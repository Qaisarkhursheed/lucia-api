<?php

namespace App\Mail\Copilot;

use App\Mail\MailableBase;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewHireRequestAvailableMail extends MailableBase implements ShouldQueue
{
    private AdvisorRequest  $AdvisorRequest ;
    private User $user;

    /**
     * Create a new message instance.
     *
     * @param AdvisorRequest $AdvisorRequest
     * @param User $user
     */
    public function __construct(AdvisorRequest  $AdvisorRequest,User $user )
    {
        $this->AdvisorRequest  = $AdvisorRequest ;
        $this->user  = $user;

        $this->subject =   $this->ucwordsSubject( "New request from "  .  $this->AdvisorRequest->user->first_name  );
        $this->to( $user->email );

    }

    /**
     * Build the message.
     *
     * @return NewHireRequestAvailableMail
     */
    public function build()
    {
        return $this->view( "mails.copilot.new_hire_request_available" )
            ->with( 'advisorRequest', $this->AdvisorRequest  )
            ->with( 'user', $this->user  );
    }
}
