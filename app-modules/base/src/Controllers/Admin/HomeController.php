<?php

namespace Modules\Base\Controllers\Admin;

use Auth;

class HomeController extends \BaseController
{
    public function dashboard()
    {
        // if (!Auth::user()->roleIs('admin')) {
        //     return redirect(route('admin.product.database'));
        // }

        $this->data['title'] = trans('backpack::base.dashboard'); // set the page title

        return view('backpack::dashboard', $this->data);
    }

    /**
     * Redirect to the dashboard.
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        // if (!Auth::user()->roleIs('admin')) {
        //     return redirect(route('admin.product.database'));
        // }

        // The '/admin' route is not to be used as a page, because it breaks the menu's active state.
        return redirect(config('backpack.base.route_prefix').'/dashboard');
    }
}
