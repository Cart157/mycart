<?php

namespace Modules\Mall\Controllers\Api;

use Modules\Mall\Models;
use Request;

class TopicController extends \BaseController
{
    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            if (Request::has('limit')) {
                $take_num = Request::input('limit');
            } else {
                $take_num = 5;
            }

            $topic = Models\Topic::select('id', 'name', 'image', 'sort_order')->orderBy('sort_order', 'asc')->take($take_num)->get();

            $res['data'] = $topic;
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function show($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            $topic = Models\Topic::select('id', 'name', 'image', 'sort_order')->where('id', $id)->first();

            $res['data'] = $topic;
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}