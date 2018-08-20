<?php

namespace Modules\Mall\Controllers\Store;

class BrandController extends \BaseController
{
    public function index()
    {
        return view('mall::store.brand.index');
    }
}