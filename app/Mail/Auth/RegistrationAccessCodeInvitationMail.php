<?php

namespace App\Mail\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationAccessCodeInvitationMail extends Mailable implements ShouldQueue
{
	use Queueable, SerializesModels;


	/**
	 * @var string
     */
	private string $code;

    /**
     * RegistrationAccessCodeInvitationMail constructor.
     *
     * @param string $code
     * @param string $email
     */
	public function __construct(string $code,  string $email )
	{
		$this->code = $code;
		$this->subject( $this->ucwordsSubject( "INVITATION | REGISTRATION ACCESS CODE | " . env( 'APP_NAME' ) ) );
		$this->from( env( "MAIL_FROM_ADDRESS" ), env( "MAIL_FROM_NAME" ) );
		$this->to( $email );
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->view('mails.auth.registration_access_code_invitation' )
            ->with( "code" , $this->code );
	}


}
