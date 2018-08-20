<?php

namespace Modules\Base\Middleware;

use Closure;
use Auth;
use Session;

class Admin
{
    public function handle($request, Closure $next, $guard = null)
    {
        $auth = false;

        if (Auth::check() && Session::get('admin_auth')
          && (!config('backpack.base.admin_auth_expire') || time() - Session::get('admin_auth') < config('backpack.base.admin_auth_expire'))) {
            $auth = true;
        }

        if (!$auth) {
            if ($request->ajax()) {
                return response()->json(['admin_auth_expired'], 401);
            } else {
                return redirect()->guest(config('backpack.base.route_prefix', 'admin').'/login');
            }
        }

        if (!Auth::user()->roleIs('admin') && !Auth::user()->roleIs('kuguan')) {
            Auth::logout();
            return redirect()->guest(config('backpack.base.route_prefix', 'admin').'/login');
        }

        if (!Auth::user()->roleIs('admin') && !starts_with($request->path(), 'admin/product')) {
            return redirect(route('admin.product.database'));
        }

        Session::put('admin_auth', time());

        return $next($request);
    }
}
