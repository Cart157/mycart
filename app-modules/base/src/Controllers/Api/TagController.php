<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Models;
use Request;

class TagController extends \BaseController
{
    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $tag = Models\Tag::search(Request::all());

            $res['data'] = $tag;
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function webIndex()
    {
        $search_term = Request::input('q');
        $page = Request::input('page');

        if ($search_term)
        {
            $results = Models\Tag::whereRaw("CONCAT(IFNULL(name, ''), IFNULL(item_no, '')) like ?", ['%'.$search_term.'%'])->paginate(10);
        }
        else
        {
            $results = Models\Tag::paginate(10);
        }

        return $results;
    }
}
