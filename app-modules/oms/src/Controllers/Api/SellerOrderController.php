<?php

namespace Modules\Mall\Controllers\Api;

use Modules\Mall\Models;
use Modules\Base\Models as BaseModels;
use Request;
use Validator;
use JWTAuth;
use Illuminate\Validation\Rule;

class SellerOrderController extends \BaseController
{
    /**
     * 订单列表
     */
    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            $condition = Request::all();
            $condition['seller_id'] = JWTAuth::user()->id;

            $res['data'] = Models\Order::search($condition)->each(function($item) {
                foreach ($item->goods as $key => $goods) {
                    unset($item->goods[$key]->id);
                    unset($item->goods[$key]->order_id);
                    unset($item->goods[$key]->goods_amount);
                    unset($item->goods[$key]->created_at);
                    unset($item->goods[$key]->updated_at);
                    unset($item->goods[$key]->deleted_at);
                }
            });

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 订单详情
     */
    public function show($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            $order = Models\Order::select('id', 'pay_no', 'user_id', 'order_no', 'waybill_no', 'order_status', 'evaluation_status', 'refund_status', 'refund_record', 'goods_amount', 'freight', 'order_amount', 'created_at', 'deliver_at', 'finished_at')->findOrFail($id);

            if ($order->seller_id != JWTAuth::user()->id) {
                abort(403, '没有权限！');
            }
            $order->payment_time = $order->payNo->pay_time;
            $order->consignee_name = $order->expansion->consignee_name;
            $consignee_info = json_decode($order->expansion->consignee_info, true);
            $order->consignee_mobile = $consignee_info['mb_phone'];
            $order->consignee_address = $consignee_info['area_info'].' '.$consignee_info['address'];

            unset($order->pay_no, $order->payNo, $order->user_id, $order->expansion);

            foreach ($order->goods as $key => $goods) {
                if ($order->order_status < 20) {
                    $is_allow_refund = false;
                } else {
                    $refund = Models\Refund::where('order_id', $id)->where('goods_id', $goods->goods_id)->where('refund_status', 1)->first();
                    if ($refund) {
                        $is_allow_refund = false;
                    } else {
                        $is_allow_refund = true;
                    }
                }

                $order->goods[$key]->is_allow_refund = $is_allow_refund;
                unset($order->goods[$key]->id, $order->goods[$key]->order_id, $order->goods[$key]->goods_amount, $order->goods[$key]->created_at, $order->goods[$key]->updated_at, $order->goods[$key]->deleted_at);
            }

            $res['data'] = $order;
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    //修改订单：改成待付款（仅在定金已付的情况下）
    public function update($id)
    {
        $res = parent::apiCreatedResponse();

        //$array = ['order_status' => 0];

        $validator = Validator::make(Request::all(), [
            'order_status' => [
                Rule::in([10]),
            ],
            'order_amount' => 'numeric',
        ]);

        try {
            if($validator->fails()) {
                abort(500, '非法操作，不能更新');
            }

            $user_id = JWTAuth::user()->id;

            // 检查订单是否是该用户的
            $order = Models\Order::where('id', $id)->where('seller_id', $user_id)->first();
            if (!$order) {
                abort(500, '非法操作，该订单不是你的，无法更新');
            }

            if ($order->order_status == 15) {
                $order->order_status = Request::input('order_status');  // 10为待付款
            } else {
                abort(403, '非法操作，不能更新bb');
            }

            $order->save();
        } catch (Exception $e) {
            $res = parent::apiException($e, $res);
        }
        return $res;
    }
}
