<?php

namespace App\Mail\Auth;

use App\Mail\MJMLMailable;
use App\ModelsExtended\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class AccountPendingMail extends MJMLMailable implements ShouldQueue
{
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
        $this->subject( $this->ucwordsSubject( "ACCOUNT PENDING | " . env( 'APP_NAME' ) ) );
        $this->from( env( "MAIL_FROM_ADDRESS" ), env( "MAIL_FROM_NAME" ) );
        $this->to( $user->email );
    }

    /**
     * Build the message.
     *
     * @return MJMLMailable
     */
    public function build()
    {
        return $this->mjmlBlade('account_pending' )
            ->with( "user" , $this->user );
    }
}
