<?php

namespace App\Mail\Auth;

use App\Mail\MailableBase;
use App\ModelsExtended\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class AccountRejectedMail extends MailableBase
{
    use Queueable, SerializesModels;

    /**
     * @var User|object
     */
    private $user;

    /**
     * RequestEmailValidation constructor.
     * @param $user User | object
     */
    public function __construct( $user )
    {
        $this->user = $user;
        $this->subject( $this->ucwordsSubject( "ACCOUNT Waitlisted | " . env( 'APP_NAME' ) ) );
        $this->from( env( "MAIL_FROM_ADDRESS" ), env( "MAIL_FROM_NAME" ) );
        $this->to( $user->email );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.auth.account_rejected' )
            ->with( "user" , $this->user );
    }
}
