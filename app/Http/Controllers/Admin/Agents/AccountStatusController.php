<?php

namespace App\Http\Controllers\Admin\Agents;

use App\Events\UserStatusChangedEvent;
use App\Http\Controllers\Controller;
use App\Http\Responses\OkResponse;
use App\Mail\Auth\AccountPasswordRegeneratedMail;
use App\ModelsExtended\AccountStatus;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\UnauthorizedException;

class AccountStatusController extends Controller
{
    /**
     * @var User|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    private $agent;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|User
     */
    private $user;

    /**
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function __construct(Request $request)
    {
        $this->validatedRules([
            'email' => 'required|email|max:200|exists:users,email',
        ]);

        $this->agent = User::getAgent( $request->input('email') );
        if( ! $this->agent ) throw new \Exception( "Please, pass in a valid agent's email" );

        $this->user = auth()->user();
        self::verifyAgentIsInMyAccount( $this->user, $this->agent );
    }

    /**
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function approve()
    {
        $this->setStatus( AccountStatus::APPROVED );
        return new OkResponse();
    }

    /**
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function reject()
    {
        $this->setStatus( AccountStatus::REJECTED );
        return new OkResponse();
    }

    /**
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function resetPassword()
    {
        // you can only do this if the account is approved.
        if ( $this->agent->account_status_id !== AccountStatus::APPROVED )
            throw new \Exception( "The user is NOT approved yet!" );

        Mail::send( new AccountPasswordRegeneratedMail( $this->agent ) );
        return new OkResponse();
    }

    /**
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function impersonate()
    {
        // you can only do this if the account is approved.
        if ( $this->agent->account_status_id !== AccountStatus::APPROVED )
            throw new \Exception( "The user is NOT approved yet!" );

        $this->agent->update([
            "impersonation_token" => md5( $this->agent->id  . '-' . Carbon::now()->timestamp ),
		    'impersonation_token_expiry' => Carbon::now()->addMinutes(5)
        ]);

        return new OkResponse( [
            "impersonation_token" => $this->agent->impersonation_token,
            "expires_utc" => $this->agent->impersonation_token_expiry,

            "apiUrl" => Str::of( env( "APP_URL" )  )->finish( '/' )
                . 'auth/impersonate/' . $this->agent->impersonation_token,
            "appUrl" => Str::of( env( "UI_APP_URL" )  )->finish( '/' )
                . 'auth/impersonate/' . $this->agent->impersonation_token
        ] );
    }


    /**
     * @throws \Exception
     */
    public function deleteAccount( )
    {

        return DB::transaction(function () {

            // first delete role
            $this->agent->deleteRole( Role::Agent );
            $this->agent->refresh();

            // if no more roles, delete account
            if( !$this->agent->roles->count() ) {
                $this->agent->delete();
                return new OkResponse();
            }

            return new OkResponse($this->agent->presentForDev());

        });

    }

    /**
     * @param int $account_status_id
     * @throws \Exception
     */
    private function setStatus( int $account_status_id )
    {
        if ( $this->agent->account_status_id === $account_status_id )
            throw new \Exception( "The user already has same status!" );

        $this->agent->update([
            'account_status_id' => $account_status_id
        ]);

        // raise event
        event(new UserStatusChangedEvent( $this->agent ));
    }

    /**
     * @return void
     */
    public static function verifyAgentIsInMyAccount(User $user, User $agent )
    {
        if($user->isLoggedInAsMasterAccount() )
        {
            // confirm if the agent is in my Master Account
            if ($user->masterAccountId() != $agent->masterAccountId())
                throw new UnauthorizedException("You can not operate another agent in a different account!");
        }
    }

}
