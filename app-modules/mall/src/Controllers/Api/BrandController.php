<?php

namespace Modules\Mall\Controllers\Api;

use Modules\Mall\Models;
use Request;

class BrandController extends \BaseController
{
    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            $res['data'] = Models\Brand::treeArray();

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function cloudBrand()
    {
        $search_term = Request::input('q');
        $page = Request::input('page');

        if ($search_term)
        {
            $results = Models\Brand::where('type', 0)->where('name', 'LIKE', '%'.$search_term.'%')->paginate(10);
        }
        else
        {
            $results = Models\Brand::where('type', 0)->paginate(10);
        }

        return $results;
    }

    public function cloudSeries($id)
    {
        $search_term = Request::input('q');
        $page = Request::input('page');

        $series = Models\Brand::where('parent_id', $id);

        if ($search_term)
        {
            $results = $series->where('name', 'LIKE', '%'.$search_term.'%')->paginate(10);
        }
        else
        {
            $results = $series->paginate(10);
        }

        return $results;
    }
}
