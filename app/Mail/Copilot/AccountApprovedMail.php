<?php

namespace App\Mail\Copilot;

use App\Mail\MailableBase;
use App\ModelsExtended\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class AccountApprovedMail extends MailableBase implements ShouldQueue
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
		$this->user = $user->refresh();
		$this->subject( $this->ucwordsSubject( "CONGRATS | ACCOUNT APPROVED | " .  env( 'APP_NAME' ) ) );
		$this->from( env( "MAIL_FROM_ADDRESS" ), env( "MAIL_FROM_NAME" ) );
		$this->to( $user->email );
	}

	/**
	 * Build the message.
	 *
	 * @return AccountApprovedMail|\Illuminate\View\View|\Laravel\Lumen\Application
	 */
	public function build()
	{
        $password = \App\Mail\Auth\AccountApprovedMail::shouldRecreatePasswordOnNewRoleAdded( $this->user ) ?
            \App\Mail\Auth\AccountApprovedMail::createNewPasswordOnUser($this->user) :
            \App\Mail\Auth\AccountApprovedMail::SKIP__PASSWORD__SECTION;

        return $this->view( "mails.copilot.account_approved" )
            ->with( "user" , $this->user )
            ->with( "password" , $password );
	}


}
