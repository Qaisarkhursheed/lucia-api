<?php

namespace App\Mail\Copilot;

use App\Mail\MailableBase;
use App\ModelsExtended\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class StripeConnectionReminderMail extends MailableBase implements ShouldQueue
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
		$this->subject( "Remember to connect your Stripe account");
		$this->from( env( "MAIL_FROM_ADDRESS" ), env( "MAIL_FROM_NAME" ) );
		$this->to( $user->email );
	}

	/**
	 * Build the message.
	 *
	 * @return StripeConnectionReminderMail
     */
	public function build()
	{
		return $this->view('mails.copilot.stripe_connection_reminder' )
            ->with( "user" , $this->user );
	}
}
