<?php

namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;

class SampleMail extends MJMLMailable implements ShouldQueue
{
    /**
     * Create a new message instance.
     * @param string $target_email
     */
    public function __construct(string $target_email = "ibukunoreofe@gmail.com" )
    {

        $this->subject = "TESTING SMTP | " . env( "APP_NAME" ) ;
        $this->to( $target_email );

    }

    /**
     * Build the message.
     *
     * @return SampleMail|\Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function build()
    {
        return $this->view( "mails.sample_mjml_mail" );
    }
}
