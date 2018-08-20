<?php

namespace Modules\Base\Controllers\Front;

class HomeController extends \BaseController
{
    public function index()
    {
        return redirect('/index.html');
    }

    public function download()
    {
        return view('base::app.download');
    }

    public function downloadFromHome()
    {
        return view('base::app.download_from_home');
    }

    public function downloadFromSummary()
    {
        return view('base::app.download_from_summary');
    }
}
