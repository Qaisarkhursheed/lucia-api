<?php

namespace App\Mail;

use App\Mail\MailableBase;
use App\ModelsExtended\AdvisorRequest;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewRequestReceivedForAllMail extends MailableBase implements ShouldQueue
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

        $this->subject =   $this->ucwordsSubject( "New request from "  .  $this->AdvisorRequest->user->first_name  );
        $this->to( $user->email );

    }

    /**
     * Build the message.
     *
     * @return NewRequestReceivedForAllMail
     */
    public function build()
    {
        return $this->view( "mails.new_request_received_to_all_copilots" )
            ->with( 'advisorRequest', $this->AdvisorRequest )->with('user', $this->User);
    }
}
