<?php

namespace App\Mail;

use App\ModelsExtended\AdvisorChat;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewConciergeMessageMail extends MailableBase implements ShouldQueue
{
    private AdvisorChat $advisorChat;

    /**
     * Create a new message instance.
     *
     * @param AdvisorChat $advisorChat
     */
    public function __construct(AdvisorChat $advisorChat )
    {
        $this->advisorChat = $advisorChat;

        $this->subject =  "You have a new message from " . $advisorChat->sender->first_name;
        $this->to( $advisorChat->receiver->email );

    }

    /**
     * Build the message.
     *
     * @return NewConciergeMessageMail
     */
    public function build()
    {
        return $this->view( "mails.new_concierge_message" )
            ->with( 'advisorChat', $this->advisorChat );
    }
}
