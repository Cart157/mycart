<?php

namespace Modules\Base\Controllers\Common\Payment;

use Modules\Base\Models\PayNo;
use Modules\Oms\Models\Order;
use Request;
use Event;
use Pay;
use Carbon\Carbon;
use Yansongda\Pay\Gateways\Wechat\Support;

class WechatController extends \BaseController
{
    /**
     * 支付异步通知
     * @url payment/wechat/notify
     * @throws \Exception
     */
    public function notify(Request $request)
    {
        $current_time = date('Y-m-d H:i:s');
        Request::merge(Support::fromXml(Request::getContent()));
        Request::merge([
            'out_trade_no' => strstr(Request::input('out_trade_no') , '_', true),
            'total_amount' => (string) bcdiv(Request::input('total_fee'), 100, 2),
        ]);

        file_put_contents(storage_path('wxpay_notify.txt'), "[{$current_time}]收到来自微信支付的异步通知\r\n", FILE_APPEND);
        file_put_contents(storage_path('wxpay_notify.txt'), "[{$current_time}]收到数据：". var_export(Request::all(), true), FILE_APPEND);

        try {
            Pay::wechat()->verify();

            if (Request::input('trade_type') == 'APP') {
                if (Request::input('mch_id') != config('pay.wechat_app.mch_id')) {
                    abort(403, '异常的APP商户号：'. Request::input('mch_id'));
                }
            } else {
                if (Request::input('mch_id') != config('pay.wechat.mch_id')) {
                    abort(403, '异常的非APP商户号：'. Request::input('mch_id'));
                }
            }

            // 取得支付号
            $pay_no = PayNo::where('pay_no', Request::input('out_trade_no'))
                           ->first();

            // 取得失败时
            if (!$pay_no) {
                abort(403, '该支付号不存在');
            }

            // 防止重复通知导致的重复处理
            if (!is_null($pay_no->pay_time)) {
                abort(403, '该支付号已被处理过了，再次接到通知，通知状态：'. Request::input('result_code'));
            }

            // 请自行对 result_code，交易是否成功需要查看result_code(SUCCESS/FAIL)来判断
            if (Request::input('result_code') != 'SUCCESS') {
                abort(403, '收到未知的交易状态：'. Request::input('result_code') .'/'. Request::input('return_code'));
            }

            if (Request::input('total_amount') != $pay_no->pay_amount) {
                abort(403, '支付的金额和应收的不符，请检查代码寻找原因');
            }

            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 订单通知
            if ($pay_no->pay_type == 'order') {
                $order = $pay_no->order;
                if (!$order) {
                    abort(403, '支付号对应的订单不存在');
                }

                // 待发货
                if ($order->order_status == 10) {
                    $order->order_status = 20; // 待发货
                    $order->save();


                    # start 修复订单：支付成功后添加flow
                    if ($order->order_type == 30 && $order->careLog->order_type == 5) {
                         # start订单追踪信息
                        order_flow([
                            'sort_order'    => 70,
                            //订单id
                            'order_id'      => $order->careLog->id,
                            'order_type'    => 30,
                            //订单所属用户的id
                            'op_user_id'    => $order->careLog->user_id,
                            //操作记录，要显示的内容
                            'op_log'        => '付款成功，等待洗护',
                            //操作详情
                            'op_info'       => [
                                'type'        => 'waitting-care',
                                'care_log_id' => $log->id,
                            ],
                            'created_at'    => date("Y-m-d H:i:s",time())
                        ]); 
                        $log_info->status = 80;

                        order_flow([
                            'sort_order'    => 80,
                            //订单id
                            'order_id'      => $order->careLog->id,
                            'order_type'    => 30,
                            //订单所属用户的id
                            'op_user_id'    => $order->careLog->service_user_id,
                            //操作记录，要显示的内容
                            'op_log'        => '您的鞋子正在洗护中',
                            //操作详情
                            'op_info'       => [
                                'type'        => 'caring',
                                'care_log_id' => $log_info->id,
                                'care_start_time' => date("Y-m-d H:i:s", strtotime("+6 hours"))
                            ],
                            'created_at'    => date("Y-m-d H:i:s", strtotime("+6 hours"))
                        ]);
                        
                        order_flow([
                            'sort_order'    => 90,
                            //订单id
                            'order_id'      => $order->careLog->id,
                            'order_type'    => 30,
                            //订单所属用户的id
                            'op_user_id'    => $order->careLog->service_user_id,
                            //操作记录，要显示的内容
                            'op_log'        => '您的鞋子洗护完毕',
                            //操作详情
                            'op_info'       => [
                                'type'        => 'cared',
                                'care_log_id' => $log_info->id,
                                'care_end_time' => date("Y-m-d H:i:s", strtotime("+8 hours"))
                            ],
                            'created_at'    => date("Y-m-d H:i:s", strtotime("+8 hours"))
                        ]);
                        $log_info->status = 90;
                    }
                    # end 修复订单：支付成功后添加flow

                    if (class_exists('Modules\Oms\Events\OrderPaid')) {
                        Event::dispatch(new \Modules\Oms\Events\OrderPaid($order));
                    }

                    if (class_exists('Modules\Activity\Events\PoolbuyOrderPaid')) {
                        Event::dispatch(new \Modules\Activity\Events\PoolbuyOrderPaid($order));
                    }
                } else {
                    abort(403, '其他状态的订单也收到了支付通知，订单ID：'. $order->id);
                }
            } elseif ($pay_no->pay_type == 'custom_deposit') {
                $custom_log = $pay_no->custom_log;
                if (!$custom_log) {
                    abort(403, '支付号对应的定制需求不存在');
                }

                if ($custom_log->status == 10) {
                    $custom_log->status = 20;
                    $custom_log->save();
                } else {
                    abort(403, '其他状态的定制单也收到了支付通知，定制单ID：'. $custom_log->id);
                }
            }



            // $pay_no->pay_time = Carbon::now();
            $pay_no->pay_time = Carbon::parse(Request::input('time_end'));
            $pay_no->payment_trade_no = Request::input('transaction_id');
            $pay_no->save();

            file_put_contents(storage_path('wxpay_notify.txt'), "[{$current_time}]处理成功\r\n\r\n\r\n", FILE_APPEND);

        } catch (\Exception $e) {
            file_put_contents(storage_path('wxpay_notify.txt'), "[{$current_time}]处理失败：{$e->getMessage()}\r\n\r\n\r\n", FILE_APPEND);
            file_put_contents(storage_path('wxpay_notify_error.txt'), "[{$current_time}]处理失败：{$e->getMessage()}\r\n\r\n\r\n", FILE_APPEND);
        }

        exit('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>');
        return Pay::wechat()->success();
    }
}
