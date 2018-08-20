<?php

namespace Modules\Mall\Controllers\Store;

use Modules\Mall\Models;
use Modules\Oms\Models as OmsModels;
use Validator;
use Request;
use DB;
use Carbon\Carbon;

class OrderController extends \BaseController
{
    public function index()
    {
        $q = OmsModels\Order::where('store_id', 1);

        if (Request::has('goods_id')) {
            $q->whereHas('goods', function ($query) {
                $query->where('goods_id', Request::input('goods_id'));
            });
        }

        if (Request::has('goods_name')) {
            $q->whereHas('goods', function ($query) {
                $query->whereRaw("CONCAT(IFNULL(goods_name, ''), IFNULL(goods_spec, '')) like ?", ['%'.Request::input('goods_name').'%']);
            });
        }

        // 自定 状态码 ：41：需要评价、42：售后中
        if (Request::has('order_status')) {
            if (Request::input('order_status') == 41) {
                $q->where('order_status', 40)->where('evaluation_status', 1);
            } elseif (Request::input('order_status') == 42) {
                $q->where('refund_status', 1);
            } else {
                $q->where('order_status', Request::input('order_status'));
            }
        }

        if (Request::has('evaluation_status')) {
            $q->where('evaluation_status', Request::input('evaluation_status'));
        }

        if (Request::has('refund_status')) {
            $q->where('refund_status', Request::input('refund_status'));
        }

        if (Request::has('order_no')) {
            $q->where('order_no', 'like', '%'.Request::input('order_no').'%');
        }

        if (Request::has('created_from')) {
            $q->where('created_at', '>=', Request::input('created_from'));
        }

        if (Request::has('created_to')) {
            $q->where('created_at', '<=', Request::input('created_to'));
        }

        $ret = $q->orderBy('created_at', 'desc')
                 ->paginate(10);

        $result['data'] = $ret;

        return view('mall::store.order.index', $result);
    }

    public function show($id)
    {
        try {
            $order = OmsModels\Order::findOrFail($id);

            $result['data'] = $order;
            return view('mall::store.order.detail', $result);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function deliveryIndex()
    {
        $q = OmsModels\Order::where('order_status', '>=', 20);

        if (Request::has('order_no')) {
            $q->where('order_no', 'like', '%'.Request::input('order_no').'%');
        }

        if (Request::has('user_name')) {
            $q->whereHas('user', function ($query) {
                $query->where('name', 'like', '%'.Request::input('user_name').'%');
            });
        }

        if (Request::has('consignee_name')) {
            $q->whereHas('expansion', function ($query) {
                $query->where('consignee_name', 'like', '%'.Request::input('consignee_name').'%');
            });
        }

        if (Request::has('created_from')) {
            $q->where('created_at', '>=', Request::input('created_from'));
        }

        if (Request::has('created_to')) {
            $q->where('created_at', '<=', Request::input('created_to'));
        }

        if (Request::input('delivery_status') == 1) {
            $res['data'] = $q->where('order_status', '>', 20)->orderBy('deliver_at', 'desc')->paginate(10);
        } else {
            $res['data'] = $q->where('order_status', '=', 20)->orderBy('created_at', 'desc')->paginate(10);
        }

        return view('mall::store.logistics.index', $res);
    }

    public function deliveryInfo($id)
    {
        try {
            $order = OmsModels\Order::findOrFail($id);

            if ($order->order_status != 20) {
                abort(404);
            }

            $result['data'] = $order;
            return view('mall::store.logistics.delivery', $result);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function delivery($id)
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(),[
            'area_code'             => 'required|integer|digits:6',
            'area_info'             => 'required|string',
            'address'               => 'required|string',
            'zip_code'              => 'nullable|size:6',
            'name'                  => 'required|string',
            'mb_phone'              => 'required|numeric|digits:11',

            'consigner_delivery_id' => 'required|integer',
            'consigner_refund_id'   => 'required|integer',
            'logistics_company_id'  => 'required|integer',
            'waybill_no'            => 'required|numeric',
        ]);

        try {
            // 验证格式正确
            if ($validator->fails()) {
                abort(400,$validator->errors()->first());
            }

            // 验证id存在
            $order = OmsModels\Order::findOrFail($id);

            // 验证订单状态：待发货
            if ($order->order_status != 20) {
                abort(404);
            }

            $order->logistics_company_id = Request::input('logistics_company_id');
            $order->waybill_no = Request::input('waybill_no');
            $order->order_status = 30;
            $order->deliver_at = new Carbon();
            $order->save();

            $order_expansion = OmsModels\OrderExpansion::findOrFail($id);
            $consignee_info = [
                'area_code' => Request::input('area_code'),
                'area_info' => Request::input('area_info'),
                'address' => Request::input('address'),
                'mb_phone' => Request::input('mb_phone'),
                'tel_phone' => null,
                'zip_code' => Request::input('zip_code'),
            ];
            $order_expansion->consignee_name = Request::input('name');
            $order_expansion->consignee_info = json_encode($consignee_info, JSON_UNESCAPED_UNICODE);
            $order_expansion->consigner_delivery_id = Request::input('consigner_delivery_id');
            $order_expansion->consigner_refund_id = Request::input('consigner_refund_id');
            $order_expansion->memo = Request::input('memo');
            $order_expansion->save();

            return redirect('store/order?order_status=30');
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function cancel($id)
    {
        $res = parent::apiCreatedResponse();

        try {
            // 验证id存在
            $order = OmsModels\Order::findOrFail($id);

            // 验证订单状态：待付款
            if ($order->order_status != 10) {
                abort(404);
            }

            $order->order_status = 0;
            $order->save();

            //return redirect('store/order?order_status=0');
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function editPrice($id)
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(),[
            'goods_amount'      => 'required|numeric|min:0',
            'freight'           => 'required|numeric|min:0'
        ]);

        try {
            // 验证格式正确
            if ($validator->fails()) {
                abort(400,$validator->errors()->first());
            }

            // 验证id存在
            $order = OmsModels\Order::findOrFail($id);

            // 验证订单状态：待付款
            if ($order->order_status != 10) {
                abort(404);
            }

            $order->goods_amount = Request::input('goods_amount');
            $order->freight = Request::input('freight');
            $order->order_amount = $order->goods_amount + $order->freight - $order->expansion->promotion_amount;
            $order->save();

            //return redirect('store/order?order_status=10');
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
