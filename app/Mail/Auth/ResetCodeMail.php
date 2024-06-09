<?php

namespace App\Mail\Auth;

use App\Mail\MJMLMailable;
use App\ModelsExtended\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetCodeMail extends MJMLMailable implements ShouldQueue
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
		$this->subject( $this->ucwordsSubject( "RESET CODE | " . env( 'APP_NAME' ) ) );
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
		return $this->mjmlBlade('confirm_email' )
         ->with( "user" , $this->user  )
        ->with( "code" , decrypt( $this->user->password_reset_token ) );
	}


}
