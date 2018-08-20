<?php

namespace Modules\Base\Controllers\Common;

use Modules\Base\Models\PayNo;
use Modules\Oms\Models as OmsModels;
use Request;
use Validator;
use Pay;
use Carbon\Carbon;

class PayController extends \BaseController
{
    # 售后
    public function refund()
    {
        try {
            // 1. 取得退款单 by refund_id
            $validator = Validator::make(Request::all(), [
                'refund_id'     => 'required|integer',
                'refund_mod'    => 'required|in:1,2',
                'option_type'   => 'required|integer',
            ]);

            // 部分退款时，必须填写
            $validator->sometimes(['refund_payment', 'refund_trade_no'], 'required', function ($input) {
                return $input->refund_mod == 2;
            });

            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 2. 验证退款单的状态和订单的状态
            $refund = OmsModels\Refund::find(Request::input('refund_id'));
            if (!$refund) {
                abort(403, '退款单不存在');
            }

            // 验证option_type
                // option_type == 1，则refund_status == 10,detail需不存在记录
            if (Request::input('option_type') == 1) {
                if ($refund->refund_status != 10 || $refund->detail->toArray() != []) {
                    abort(403, '不能拒绝退款！');
                }
                // option_type == 3，则refund_status == 30,detail需存在两条记录
            } elseif (Request::input('option_type') == 3) {
                if ($refund->refund_status != 30 || count($refund->detail) != 2) {
                    abort(403, '不能拒绝退款！');
                }
            }

            $order = OmsModels\Order::find($refund->order_id);
            if (!$order) {
                abort(403, '要退款的订单不存在');
            }

            if ($refund->refund_status == 0 || $refund->refund_status == 40) {
                abort(403, '非法操作：该退款单已经处理过了');
            }

            // 1为全额退款
            if (Request::input('refund_mod') == 1) {
                if ($refund->refund_amount != $order->order_amount) {
                    abort(403, '非法操作：全额退款的金额应该与订单金额一致');
                }

                // TODO: 根据pay_no知道使用那个支付工具进行的退款
                $trade = [
                    'out_trade_no'  => $order->pay_no,
                    'refund_amount' => $order->order_amount,
                ];

                $ret = Pay::driver('alipay')->gateway()->refund($trade);

                if ($ret['msg'] = 'Success') {
                    // 改变状态
                    $refund->refund_status   = 40;
                    $refund->refund_payment  = 'alipay';
                    $refund->refund_trade_no = $ret['trade_no'];
                    $refund->finished_at     = $ret['gmt_refund_pay'];
                    $refund->save();

                    OmsModels\RefundDetail::create([
                        'refund_id'             => $refund->id,
                        'refund_type'           => $refund->refund_type,
                        'option_type'           => Request::input('option_type'),
                        'option_value'          => 1,
                        'seller_description'    => Request::input('seller_description'),
                    ]);

                    // 记录支付宝订单号
                    return [
                        'result'    => true,
                        'message'   => '退款成功，实退金额 ' . $ret['refund_fee'] . ' 元',
                    ];
                }
            } else {
                $refund->refund_status   = 40;
                $refund->refund_payment  = Request::input('refund_payment');
                $refund->refund_trade_no = Request::input('refund_trade_no');
                $refund->finished_at     = Carbon::now();
                $refund->save();

                OmsModels\RefundDetail::create([
                    'refund_id'             => $refund->id,
                    'refund_type'           => $refund->refund_type,
                    'option_type'           => Request::input('option_type'),
                    'option_value'          => 1,
                    'seller_description'    => Request::input('seller_description'),
                ]);

                return [
                    'result'    => true,
                    'message'   => '退款成功',
                ];
            }

        } catch (\Exception $e) {
            return [
                'result'    => false,
                'message'   => $e->getMessage(),
            ];
        }
    }
}
