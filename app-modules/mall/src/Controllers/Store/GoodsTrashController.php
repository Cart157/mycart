<?php

namespace Modules\Mall\Controllers\Store;

use Modules\Mall\Models;
use Request;
use DB;

class GoodsTrashController extends \BaseController
{
/*
    public function index()
    {
        $q = Models\GoodsSpu::onlyTrashed();

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

        return view('mall::store.goods.trash', $result);
    }

    public function batchUpdate()
    {
        // DB::table('mall_goods_spu')
        //     ->whereIn('id', Request::input('id'))
        //     ->update(['deleted_at' => null]);

        Models\GoodsSpu::whereIn('id', Request::input('id'))
                       ->restore();

        $res['result']  = true;
        $res['message'] = '恢复成功';

        return $res;
    }

    public function batchDelete()
    {
        $res['result']  = false;
        $res['message'] = '彻底删除很危险，证明你点了';

        return $res;

        // dd('彻底删除很危险，证明你已来过');
        // Models\GoodsSpu::whereIn('id', Request::input('id'))
        //                ->forceDelete();

        // $res['result']  = true;
        // $res['message'] = '彻底删除成功';

        // return $res;
    }
 */
    public function index()
    {
        $q = Models\Goods::onlyTrashed();

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

        return view('mall::store.goods.trash', $result);
    }

    public function batchUpdate()
    {
        if (empty(Request::input('id'))) {
            $res['result']  = false;
            $res['message'] = '没有选中的商品';

            return $res;
        }

        foreach (Request::input('id') as $sku_id) {
            $sku = Models\Goods::withTrashed()->find($sku_id);
            $same_color_ids = $sku->spu->sku()->withTrashed()->where('color_id', $sku->color_id)->get()->pluck('id')->toArray();

            Models\Goods::whereIn('id', $same_color_ids)->restore();
        }

        $res['result']  = true;
        $res['message'] = '恢复成功';

        return $res;
    }
/*
    public function batchDelete()
    {
        if (empty(Request::input('id'))) {
            $res['result']  = false;
            $res['message'] = '没有选中的商品';

            return $res;
        }

        foreach (Request::input('id') as $sku_id) {
            $sku = Models\Goods::withTrashed()->find($sku_id);
            $same_color_ids = $sku->spu->sku()->withTrashed()->where('color_id', $sku->color_id)->get()->pluck('id')->toArray();

            Models\Goods::whereIn('id', $same_color_ids)->forceDelete();
        }

        $res['result']  = true;
        $res['message'] = '彻底删除成功';

        return $res;
    }
 */
}