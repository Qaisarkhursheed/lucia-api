<?php

namespace App\Mail;

use App\Mail\MailableBase;
use App\ModelsExtended\AdvisorRequest;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminRequestRefunded extends MailableBase implements ShouldQueue
{
    private AdvisorRequest  $AdvisorRequest ;
    private $User ;

    /**
     * Create a new message instance.
     *
     * @param AdvisorRequest  $AdvisorRequest
     */
    public function __construct(AdvisorRequest  $AdvisorRequest , $user = null  )
    {
        $this->AdvisorRequest  = $AdvisorRequest;
        $this->User  = $user;
        $this->AdvisorRequest->user->first_name;

        $this->subject =   $this->ucwordsSubject( "Refunded request by {$this->User->first_name} {$this->User->last_name}");
        $this->to( env("ADMIN_EMAIL") );

    }

    /**
     * Build the message.
     *
     * @return NewHourlyRequestReceivedMail
     */
    public function build()
    {
        return $this->view( "mails.notify_admin_request_refunded" )
            ->with( 'advisorRequest', $this->AdvisorRequest )->with('user', $this->User);
    }
}
