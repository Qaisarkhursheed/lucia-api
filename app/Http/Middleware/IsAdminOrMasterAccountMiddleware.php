<?php

namespace App\Http\Middleware;

use App\Http\Responses\UnauthorizedResponse;
use Closure;

class IsAdminOrMasterAccountMiddleware
{
    const NOT_ALLOWED_MESSAGE = 'Unauthorized. You must be an administrator or a master account to access this URL.';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (
            auth()->guest() ||
            !( auth()->user()->isLoggedInAsAdmin() || auth()->user()->isLoggedInAsMasterAccount() )
        ) {
            return new UnauthorizedResponse(message( self::NOT_ALLOWED_MESSAGE ) );
        }
        return $next($request);
    }
}
