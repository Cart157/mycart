<?php

namespace Modules\Mall\Controllers\Admin;

use Auth;

class HomeController extends \BaseController
{
    public function index()
    {
        return redirect(route('admin.mall.setting.index'));
    }
}
