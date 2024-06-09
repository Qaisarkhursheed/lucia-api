<?php

namespace App\Mail\Auth;

use App\Mail\MJMLMailable;
use App\ModelsExtended\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class AdminAccountInvitationMail extends MJMLMailable implements ShouldQueue
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
		$this->subject( $this->ucwordsSubject( "INVITATION | ADMIN ACCOUNT CREATION | " . env( 'APP_NAME' ) ) );
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
        $password = AccountApprovedMail::shouldRecreatePasswordOnNewRoleAdded( $this->user ) ? AccountApprovedMail::createNewPasswordOnUser($this->user) : AccountApprovedMail::SKIP__PASSWORD__SECTION;

        return $this->mjmlBlade('teammate_invite' )
            ->with( "user" , $this->user )
            ->with( "password" , $password );
	}


}
