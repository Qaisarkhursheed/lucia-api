<?php

namespace App\Http\Middleware;

use App\Http\Responses\RequireLicenseResponse;
use App\ModelsExtended\User;
use Closure;

class IsLicensedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( auth()->guest() || !$this->hasValidLicense(auth()->user()) ) {
            return new RequireLicenseResponse(message( 'Unauthorized. You must have a license to access this URL.') );
        }
        return $next($request);
    }

    /**
     * @return bool
     */
    private function hasValidLicense(User $user): bool
    {
        if( ! is_true( env( 'FORCE_LICENSE_CHECK' ) ) ) return true;
        if( $user->isLoggedInAsTravelAgent())
        {
            return Authenticate::getUserRole()->has_valid_license;
        }
        return true;
    }
}
