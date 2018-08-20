<?php

namespace Apk\Base\Controllers\User;

class HomeController extends \BaseController
{
    public function getIndex()
    {
        return view('sec_base::user.index');
    }
}
