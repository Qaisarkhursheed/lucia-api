<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Middleware\IsAdminOrMasterAccountMiddleware;
use App\Http\Responses\UnauthorizedResponse;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\UnauthorizedException;

class AuthController extends \App\Http\Controllers\Auth\AuthController
{
    protected int $TARGETED_ROLE = Role::Administrator;

    protected function loginWithCredentials(string $email, string $password, int $role_id)
    {
        // Try Login Credential
        if (!$token = auth()->attempt([ 'email' => $email, 'password' => $password ])) {
            return new UnauthorizedResponse(message("Invalid Login Details!" ));
        }

        $user = auth()->user();

        // switch it here
        if( !$user->hasRole( Role::Administrator ) && $user->hasRole( Role::MasterAccount ) )
            $this->TARGETED_ROLE = Role::MasterAccount;

        //  Return logged in details
        return $this->throwExceptionIfNotQualified($user)
            ->respondWithToken($token, $this->TARGETED_ROLE );
    }

    /**
     * @param User | Authenticatable $user
     * @return AuthController
     */
    protected function throwExceptionIfNotQualified( $user): AuthController
    {
        if (
            ! $user->hasRole( Role::Administrator )
            && ! $user->hasRole( Role::MasterAccount )
            && ! $user->hasRole( Role::Super_Admin )
        ) {
            throw new UnauthorizedException( "You must be registered in as " . Role::getById($this->TARGETED_ROLE)->description .  " to login on this app section!" );
        }
        return $this;
    }
}
