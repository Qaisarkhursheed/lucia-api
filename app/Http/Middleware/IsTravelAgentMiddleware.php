<?php

namespace App\Http\Middleware;

use App\Http\Responses\UnauthorizedResponse;
use Closure;

class IsTravelAgentMiddleware
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
        if ( auth()->guest() || ! auth()->user()->isLoggedInAsTravelAgent() ) {
            return new UnauthorizedResponse(message( 'Unauthorized. You must be a travel agent to access this URL.') );
        }
        return $next($request);
    }
}
