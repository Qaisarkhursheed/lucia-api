<?php

namespace App\Http\Middleware;

use App\Http\Responses\UnauthorizedResponse;
use App\ModelsExtended\UserRole;
use App\ModelsExtended\UserSession;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Str;

class Authenticate
{

    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    public static ?string $TOKEN;

    private static ?UserSession $session = null;
    private static ?UserRole $userRole = null;

    /**
     * @return UserSession|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function getUserSession()
    {
        if( !self::$session )
            return self::$session = UserSession::getByToken( self::$TOKEN );

        return  self::$session;
    }

    /**
     * @return UserSession|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function getUserRole()
    {
        if( !self::$userRole )
            return self::$userRole = UserRole::getUserRole( self::$session->role_id, self::$session->user_id  );

        return  self::$userRole;
    }


    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
            return new UnauthorizedResponse(message( 'Unauthorized.') );
        }

        self::$TOKEN = $request->hasHeader("Authorization")?
            Str::of($request->header("Authorization"))->explode(" ")->last():
            $request->input("token");

        return $next($request);
    }
}
