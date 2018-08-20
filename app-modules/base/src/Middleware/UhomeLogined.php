<?php

namespace Modules\Base\Middleware;

use Closure;
use Auth;
use Session;

class UhomeLogined
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::check()) {
            return redirect('uhome');
        }

        return $next($request);
    }
}
