<?php

namespace Modules\Mall\Controllers\Store;

use Modules\Mall\Models;
use Modules\Oms\Models as OmsModels;

class HomeController extends \BaseController
{
    public function index()
    {
        // 实时数据
        $count['view_cnt'] = Models\Goods::sum('view_cnt');

        $count['paid_cnt'] = OmsModels\Order::where('order_status', '>=', 20)->sum('order_amount');

        $buyer_cnt = OmsModels\Order::where('order_status', '>=', 20)->groupBy('user_id')->get();
        $count['buyer_cnt'] = count($buyer_cnt);

        $res['count'] = $count;

        // 宝贝统计
        $is_sell = Models\Goods::where('is_sell', 1)->groupBy('cloud_id')->get();
        $goods['is_sell'] = count($is_sell);

        $wait_delivery = OmsModels\OrderGoods::whereHas('order', function ($query) {
            $query->where('order_status', 20);
        })->get();
        $goods['wait_delivery'] = count($wait_delivery);

        $has_delivered = OmsModels\OrderGoods::whereHas('order', function ($query) {
            $query->where('order_status', '>=', 30);
        })->get();
        $goods['has_delivered'] = count($has_delivered);

        $res['goods'] = $goods;

        // 订单统计
        $wait_delivery = OmsModels\Order::where('order_status', 20)->get();
        $order['wait_delivery'] = count($wait_delivery);

        $has_delivered = OmsModels\Order::where('order_status', '>=', 30)->get();
        $order['has_delivered'] = count($has_delivered);

        $not_finished = OmsModels\Refund::where('refund_status', '<>', 40)->where('refund_status', '<>', 0)->get();
        $order['not_finished'] = count($not_finished);

        $has_paid = OmsModels\Order::where('order_status', '>=', 20)->get();
        $order['has_paid'] = count($has_paid);

        $lastday_paid = OmsModels\Order::where('order_status', '>=', 20)->whereHas('payNo', function ($query) {
            $lastday = date('Y-m-d', strtotime('-1 day'));
            $today = date('Y-m-d');
            $query->where('pay_time', '>=', $lastday)->where('pay_time', '<=', $today);
        });
        $order['lastday_paid'] = count($lastday_paid->get());
        $order['lastday_paid_user_cnt'] = count($lastday_paid->groupBy('user_id')->get());
        $order['lastday_paid_amount_cnt'] = $lastday_paid->sum('order_amount');

        $res['order'] = $order;

        return view('mall::store.home.index', $res);
    }
}
