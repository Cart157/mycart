<?php

namespace Modules\Oms\Controllers\Admin;

class HomeController extends \BaseController
{
    public function index()
    {
        return redirect(route('admin.oms.setting.index'));
    }
}
