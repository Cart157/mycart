<?php

namespace Modules\Base\Middleware;

use Modules\Base\Models\UserBanned;
use Tymon\JWTAuth\Http\Middleware\Authenticate;
use Closure;
use JWTAuth;

class JWT extends Authenticate
{
    public function handle($request, Closure $next)
    {
        $this->authenticate($request);

        $user = JWTAuth::user();
        $banned = UserBanned::where('user_id', $user->id)->first();
        if ($banned) {
            abort(401, $banned->reason);
        }

        return $next($request);
    }
}
