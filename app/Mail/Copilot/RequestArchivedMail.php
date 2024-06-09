<?php

namespace App\Mail\Copilot;

use App\Mail\MailableBase;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\CopilotInfo;
use App\ModelsExtended\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class RequestArchivedMail extends MailableBase implements ShouldQueue
{
    private AdvisorRequest  $AdvisorRequest ;
    private User $user;
    private User $copilot;

    /**
     * Create a new message instance.
     *
     * @param AdvisorRequest $AdvisorRequest
     * @param User $user
     */
    public function __construct(AdvisorRequest  $AdvisorRequest,User $user, User $copilot )
    {
        $this->AdvisorRequest  = $AdvisorRequest ;
        $this->copilot  = $copilot;
        $this->user  = $user;

        $this->subject =   $this->AdvisorRequest->title." Request Archived";
        $this->to( $user->email );

    }
//patty.ehinger@localforeigner.com
    /**
     * Build the message.
     *
     * @return NewHireRequestAvailableMail
     */
    public function build()
    {
        return $this->view( "mails.copilot.request_archived_notification" )
            ->with( 'advisorRequest', $this->AdvisorRequest  )
            ->with( 'copilot', $this->copilot)
            ->with( 'user', $this->user  );
    }
}
