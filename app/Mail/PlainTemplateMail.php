<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlainTemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    private string $htmlMessage;

    /**
     * Create a new message instance.
     * @param array $target_email_addresses
     * @param string $message
     * @param string $subject
     */
    public function __construct(array $target_email_addresses, string $message, string $subject =  "NEW MAIL" )
    {
        $this->subject = $subject;
        $this->htmlMessage = str_replace( [ "\r\n", "\n"  ], '<br/>', $message );

        $this->to( $target_email_addresses );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view( "mails.plain_template_mail" )
            ->with( "htmlMessage" , $this->htmlMessage );
    }
}
