<?php

namespace Modules\Mall\Controllers\Api;

use Modules\Mall\Models;
use Request;

class GoodsSpuController extends \BaseController
{
    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $res['data'] = Models\GoodsSpu::search(Request::all())->each(function($item) {
                                $item->default_sku = $item->default_sku()->id;
                                unset($item->sku);
                           });

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}