<?php

namespace App\Http\Middleware;

use App\Http\Responses\UnauthorizedResponse;
use Closure;

class IsClientMiddleware
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
        if ( auth()->guest() || ! auth()->user()->isLoggedInAsClient() ) {
            return new UnauthorizedResponse(message( 'Unauthorized. You must be a client to access this URL.') );
        }
        return $next($request);
    }
}
