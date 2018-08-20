<?php

namespace Apk\Base\Controllers\Admin;

class FeedbackController extends \BaseController
{
    public function getIndex()
    {
        return view('sec_base::admin.feedback.index');
    }
}
