<?php

namespace App\Mail\Auth;

use App\Mail\MJMLMailable;
use App\ModelsExtended\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class AccountPasswordRegeneratedMail extends MJMLMailable implements ShouldQueue
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
		$this->subject( $this->ucwordsSubject( "ACCOUNT PASSWORD REGENERATED | " . env( 'APP_NAME' ) ) );
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
	    $password = Str::random(8 );
	    $this->user->update([
            'password' => app('hash')->make( $password ),
        ]);

		return $this->mjmlBlade('account_password_reset_text' )
            ->with( "user" , $this->user )
            ->with( "password" , $password );
	}
}
