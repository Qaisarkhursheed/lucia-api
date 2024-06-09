<?php

namespace App\Http\Middleware;

use App\Http\Responses\UnauthorizedResponse;
use Closure;

class IsCopilotMiddleware
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
        if ( auth()->guest() || ! auth()->user()->isLoggedInAsCopilot() ) {
            return new UnauthorizedResponse(message( 'Unauthorized. You must be a copilot to access this URL.') );
        }
        return $next($request);
    }
}
