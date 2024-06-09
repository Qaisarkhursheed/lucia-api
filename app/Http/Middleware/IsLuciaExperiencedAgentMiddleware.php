<?php

namespace App\Http\Middleware;

use App\Http\Responses\RequireLicenseResponse;
use App\ModelsExtended\AgencyUsageMode;
use Closure;

/**
 *  You can only use this request if you are an agent and using in Lucia Experience mode
 */
class IsLuciaExperiencedAgentMiddleware
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
        if ( auth()->guest() || !$this->isLuciaExperienced() ) {
            return new RequireLicenseResponse(message( 'Unauthorized. You must be an agent with Lucia Experience usage mode to access this URL.') );
        }
        return $next($request);
    }

    /**
     * @return bool
     */
    private function isLuciaExperienced(): bool
    {
       return  auth()->user()->isLoggedInAsTravelAgent() && auth()->user()->agency_usage_mode_id === AgencyUsageMode::LUCIA_EXPERIENCE ;
    }
}
