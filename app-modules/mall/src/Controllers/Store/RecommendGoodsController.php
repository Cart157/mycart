<?php

namespace Modules\Mall\Controllers\Store;

use Modules\Mall\Models;
use Request;
use DB;

class RecommendGoodsController extends \BaseController
{
    public function index()
    {
        // $q = Models\RecommendGoods::query();

        // $q->leftJoin('mall_goods_spu', 'mall_recommend_goods.spu_id', '=', 'mall_goods_spu.id')
        //   ->join('mall_goods', 'mall_goods_spu.id', '=', 'mall_goods.spu_id')->groupBy('mall_goods_spu.id');

        // $ret = $q->select(DB::raw('mall_goods_spu.*, sum(mall_goods.stock) as total_stock, sum(mall_goods.sales_cnt) as total_sales_cnt'))
        //          ->orderBy('sort_order', 'asc')
        //          ->paginate(15);

        $q = Models\GoodsSpu::query();

        if (Request::input('goods_name')) {
            $q->whereHas('sku', function ($query) {
                $query->where('name', 'like', '%'.Request::input('goods_name').'%');
            });
        }

        if (in_array(Request::input('order_by'), ['total_stock', 'sales_cnt', 'sell_time'])) {
            $order_by = Request::input('order_by');
        } else {
            $order_by = 'sort_order';
        }

        if (in_array(Request::input('order_mod'), ['asc', 'desc'])) {
            $order_mod = Request::input('order_mod');
        } else {
            $order_mod = 'asc';
        }

        $q->join('mall_goods', 'mall_goods_spu.id', '=', 'mall_goods.spu_id')
          ->rightJoin('mall_recommend_goods', 'mall_recommend_goods.spu_id', '=', 'mall_goods_spu.id')->groupBy('mall_goods_spu.id');

        $ret = $q->select(DB::raw('mall_goods_spu.*, sum(mall_goods.stock) as total_stock, sum(mall_goods.sales_cnt) as total_sales_cnt, sum(mall_goods.view_cnt) as total_view_cnt'))
                 ->orderBy($order_by, $order_mod)
                 ->paginate(15);

        $result['data'] = $ret;

        return view('mall::store.goods.recommend', $result);
    }

    public function batchUpdate()
    {
        if (empty(Request::input('id'))) {
            $res['result']  = false;
            $res['message'] = '没有选中的商品';

            return $res;
        }

        $goods_spu = Models\GoodsSpu::whereIn('id', Request::input('id'))->get();

        foreach ($goods_spu as $spu) {
            Models\RecommendGoods::firstOrCreate([
                'goods_id'  => $spu->default_sku()->id,
                'spu_id'    => $spu->id,
            ]);
        }

        Models\GoodsSpu::whereIn('id', Request::input('id'))
                       ->update(['is_lock' => 1]);

        $res['result']  = true;
        $res['message'] = '推荐成功';

        return $res;
    }

    public function batchDelete()
    {
        if (empty(Request::input('id'))) {
            $res['result']  = false;
            $res['message'] = '没有选中的商品';

            return $res;
        }

        Models\RecommendGoods::whereIn('spu_id', Request::input('id'))
                       ->forceDelete();

        $res['result']  = true;
        $res['message'] = '取消推荐成功';

        return $res;
    }
}