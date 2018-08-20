<?php

namespace Modules\Mall\Controllers\App;

use Modules\Mall\Models;
use Request;
use Validator;
use JWTAuth;
use DB;
use Carbon\Carbon;

class GoodsController extends \BaseController
{
    /**
     * 显示商品详细
     */
    public function show($id)
    {
        $goods = Models\Goods::find($id);

        if (!$goods) {
            abort(404, '商品不存在');
        }

        return view('mall::app.goods.show', ['goods' => $goods]);
    }
}