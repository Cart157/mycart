<?php

namespace Modules\Base\Middleware;

use Modules\Base\Models\UserBanned;
use Tymon\JWTAuth\Http\Middleware\Authenticate;
use Closure;
use Auth;
use JWTAuth;

class Uhome extends Authenticate
{
    public function handle($request, Closure $next)
    {
        $this->authenticate($request);
        
        // $old_token = JWTAuth::getToken();  
        // $token = JWTAuth::refresh($old_token);  
        // JWTAuth::invalidate($old_token);

        $user = JWTAuth::user();

        if (!Auth::user()->roleIs('author')) {
            abort(403, '您的角色无权登录这个系统');
        }
        return $next($request);
    }
}
