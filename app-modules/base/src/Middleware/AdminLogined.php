<?php

namespace Modules\Base\Middleware;

use Closure;
use Auth;
use Session;

class AdminLogined
{
    public function handle($request, Closure $next, $guard = null)
    {
        $auth = false;

        if (Auth::check() && Session::get('admin_auth')
          && (!config('backpack.base.admin_auth_expire') || time() - Session::get('admin_auth') < config('backpack.base.admin_auth_expire'))) {
            $auth = true;
        }

        if ($auth) {
            // if (Session::get('admin_role') === 'admin') {
                return redirect(config('backpack.base.route_prefix', 'admin'));
            // }
        }

        return $next($request);
    }
}
