<?php

namespace Modules\Mall\Controllers\Store;

use Modules\Mall\Models;
use Request;
use DB;
use Carbon\Carbon;

class GoodsOfflineController extends \BaseController
{
/*
    public function index()
    {
        $q = Models\GoodsSpu::where('mall_goods_spu.is_sell', 0);

        if (Request::input('goods_name')) {
            $q->whereHas('sku', function ($query) {
                $query->where('name', 'like', '%'.Request::input('goods_name').'%');
            });
        }

        if (in_array(Request::input('order_by'), ['total_stock', 'sales_cnt', 'sell_time'])) {
            $order_by = Request::input('order_by');
        } else {
            $order_by = 'sell_time';
        }

        if (in_array(Request::input('order_mod'), ['asc', 'desc'])) {
            $order_mod = Request::input('order_mod');
        } else {
            $order_mod = 'desc';
        }

        $q->join('mall_goods', 'mall_goods_spu.id', '=', 'mall_goods.spu_id')->groupBy('mall_goods_spu.id');

        $ret = $q->select(DB::raw('mall_goods_spu.*, sum(mall_goods.stock) as total_stock, sum(mall_goods.sales_cnt) as total_sales_cnt'))
                 ->orderBy($order_by, $order_mod)
                 ->paginate(15);

        $result['data'] = $ret;

        return view('mall::store.goods.offline', $result);
    }

    public function batchUpdate()
    {
        if (empty(Request::input('id'))) {
            $res['result']  = false;
            $res['message'] = '没有选中的商品';

            return $res;
        }

        Models\GoodsSpu::whereIn('id', Request::input('id'))
                       ->update(['is_sell' => 1]);

        $res['result']  = true;
        $res['message'] = '上架成功';

        return $res;
    }

    public function batchDelete()
    {
        if (empty(Request::input('id'))) {
            $res['result']  = false;
            $res['message'] = '没有选中的商品';

            return $res;
        }

        Models\GoodsSpu::destroy(Request::input('id'));

        $res['result']  = true;
        $res['message'] = '删除成功';

        return $res;
    }
 */
    public function index()
    {
        $q = Models\Goods::where('is_sell', 0);

        if (Request::input('goods_name')) {
            $q->where('name', 'like', '%'.Request::input('goods_name').'%');
        }

        if (in_array(Request::input('order_by'), ['total_stock', 'sales_cnt', 'sell_time'])) {
            $order_by = Request::input('order_by');
        } else {
            $order_by = 'sell_time';
        }

        if (in_array(Request::input('order_mod'), ['asc', 'desc'])) {
            $order_mod = Request::input('order_mod');
        } else {
            $order_mod = 'desc';
        }

        $result['data'] = $q->select(DB::raw('mall_goods.*, sum(stock) as total_stock, sum(sales_cnt) as total_sales_cnt'))
                            ->groupBy('spu_id', 'color_id')
                            ->orderBy($order_by, $order_mod)
                            ->paginate(15);

        return view('mall::store.goods.offline', $result);
    }

    public function batchUpdate()
    {
        if (empty(Request::input('id'))) {
            $res['result']  = false;
            $res['message'] = '没有选中的商品';

            return $res;
        }

        foreach (Request::input('id') as $sku_id) {
            $sku = Models\Goods::find($sku_id);
            $same_color_ids = $sku->spu->sku()->where('color_id', $sku->color_id)->get()->pluck('id')->toArray();

            Models\Goods::whereIn('id', $same_color_ids)->update(['is_sell' => 1, 'sell_time' => new Carbon()]);
        }

        $res['result']  = true;
        $res['message'] = '上架成功';

        return $res;
    }

    public function batchDelete()
    {
        if (empty(Request::input('id'))) {
            $res['result']  = false;
            $res['message'] = '没有选中的商品';

            return $res;
        }

        foreach (Request::input('id') as $sku_id) {
            $sku = Models\Goods::find($sku_id);
            $same_color_ids = $sku->spu->sku()->where('color_id', $sku->color_id)->get()->pluck('id')->toArray();

            Models\Goods::destroy($same_color_ids);
        }

        $res['result']  = true;
        $res['message'] = '删除成功';

        return $res;
    }
}