<?php

namespace App\Mail\Copilot;

use App\Mail\MailableBase;
use App\ModelsExtended\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class StripeAccountConnectedMail extends MailableBase implements ShouldQueue
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
		$this->subject( "Successful Stripe Connection" );
		$this->from( env( "MAIL_FROM_ADDRESS" ), env( "MAIL_FROM_NAME" ) );
		$this->to( $user->email );
	}

	/**
	 * Build the message.
	 *
	 * @return StripeAccountConnectedMail
     */
	public function build()
	{
		return $this->view('mails.copilot.stripe_connected' )
            ->with( "user" , $this->user );
	}
}
