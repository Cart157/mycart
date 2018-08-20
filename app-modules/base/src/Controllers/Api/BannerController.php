<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Models;

class BannerController extends \BaseController
{
    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $banner = Models\Banner::select('type_id', 'item_id', 'image', 'sort_order')
                ->where('type_id',9)// 9 首页
                ->where('item_id',0)
                ->orderBy('sort_order')->get();

            $res['data'] = $banner;
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}