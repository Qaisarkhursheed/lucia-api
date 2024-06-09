<?php

namespace App\Http\Middleware;

use App\Http\Responses\UnauthorizedResponse;
use Closure;

class IsAdminMiddleware
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
        if ( auth()->guest() || ! auth()->user()->isLoggedInAsAdmin() ) {
            return new UnauthorizedResponse(message( 'Unauthorized. You must be an administrator to access this URL.') );
        }
        return $next($request);
    }
}
