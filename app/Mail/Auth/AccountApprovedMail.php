<?php

namespace App\Mail\Auth;

use App\Mail\MJMLMailable;
use App\ModelsExtended\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class AccountApprovedMail extends MJMLMailable implements ShouldQueue
{
    public const SKIP__PASSWORD__SECTION = "SKIP__PASSWORD__SECTION";

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
     * Regenerate password if it is same day
     *
     * @param User $user
     * @return bool
     */
    public static function shouldRecreatePasswordOnNewRoleAdded(User $user): bool
    {
        return $user->roles->count()===1;
//        return $user->created_at->isSameDay( $user->lastestUserRole()->created_at );
    }

    /**
     * @param User $user
     * @return string
     */
    public static function createNewPasswordOnUser(User $user): string
    {
        $password = 'SKIP__PASSWORD__SECTION';//Str::random(8 );
        // $user->update([
        //     'password' => app('hash')->make( $password ),
        // ]);
        return $password;
    }

	/**
	 * Build the message.
	 *
	 * @return AccountApprovedMail|MJMLMailable|\Illuminate\View\View|\Laravel\Lumen\Application
	 */
	public function build()
	{
	    $password = self::shouldRecreatePasswordOnNewRoleAdded( $this->user ) ? self::createNewPasswordOnUser($this->user) : self::SKIP__PASSWORD__SECTION;

		return $this->mjmlBlade( "account_confirmed" )
            ->with( "user" , $this->user )
            ->with( "password" , $password );
	}


}
