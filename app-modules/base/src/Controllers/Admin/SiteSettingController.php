<?php

namespace Apk\Base\Controllers\Admin;

use Apk\Base\Logics;
use Apk\Base\Models;

class SiteSettingController extends \BaseController
{
    public function getIndex()
    {
        $result = Models\SiteSetting::all();
        return view('sec_base::admin.site_setting.index')->with('result', $result);
    }

    public function postIndex()
    {
        $logic = new Logics\Admin\SiteSetting\PostIndex();
        $result = $logic->run();

        return $result;
    }
}
