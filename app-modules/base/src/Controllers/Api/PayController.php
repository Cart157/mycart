<?php

namespace Modules\Base\Controllers\Api;

use Illuminate\Validation\Rule;
use Modules\Base\Models\PayNo;
use Modules\Oms\Models\Order;
use Request;
use Validator;
use JWTAuth;
use Pay;

class PayController extends \BaseController
{
    public function pay()
    {
        $res = parent::apiFetchedResponse();

        // 验证规则
        $validator = Validator::make(Request::all(), [
            'pay_no'        => 'digits:18',
            'order_no'      => 'digits:16',
            'driver'        => ['required', Rule::in(['alipay', 'wechat'])],
            'openid'        => 'string',
            'miniapp_name'  => 'string|in:mall,care,appraisal,customization',
        ]);

        $validator->sometimes('pay_no', ['required'], function ($input) {
            return empty($input->order_no);
        });

        $validator->sometimes('order_no', ['required'], function ($input) {
            return empty($input->pay_no);
        });

        $validator->sometimes('method', ['required', Rule::in(['web', 'wap', 'app', 'pos', 'scan', 'transfer'])], function ($input) {
            return $input->driver == 'alipay';
        });

        $validator->sometimes('method', ['required', Rule::in(['mp', 'miniapp', 'wap', 'app', 'pos', 'scan', 'transfer', 'redpack', 'groupRedpack'])], function ($input) {
            return $input->driver == 'wechat';
        });

        $validator->sometimes('openid', ['required'], function ($input) {
            return $input->driver == 'wechat' && in_array($input->method, ['mp', 'miniapp']);
        });

        $validator->sometimes('miniapp_name', ['required'], function ($input) {
            return $input->driver == 'wechat' && $input->method == 'miniapp';
        });

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $driver = Request::input('driver');
            $method = Request::input('method');

            // 二次支付，从订单列表支付
            if (Request::input('order_no')) {
                $order = Order::where('order_no', Request::input('order_no'))->first();
                if (!$order) {
                    abort(403, '订单不存在');
                }

                $pay_no = PayNo::where('pay_no', $order->pay_no)->first();
                if (!$pay_no) {
                    abort(403, '非法操作');
                }

                // // 如果是二次支付，多张订单时，重新生成支付号（先不考虑对店铺同时下单）
                // if (count($pay_no->orders) > 1) {
                //     // 循环生成支付号，查重
                //     $new_pay_no = PayNo::makePayNo(JWTAuth::user()->id);

                //     // 创建
                //     $pay_no = PayNo::create([
                //         'user_id'   => JWTAuth::user()->id,
                //         'pay_no'    => $new_pay_no,
                //     ]);

                //     $order->pay_no = $new_pay_no;
                //     $order->save();
                // }

                // 验证库存
                foreach ($order->goods as $order_goods) {
                    if ($order_goods->goods_num > $order_goods->src_goods->stock) {
                        $msg = sprintf('%s 现在库存为 %d，请重选数量'
                            , $order_goods->goods_name
                            , $order_goods->src_goods->stock
                        );
                        abort(403, $msg);
                    }
                }

            // 首次支付
            } else {
                // 取得未支付的支付号
                $pay_no = PayNo::where('pay_no', Request::input('pay_no'))->first();
                if (!$pay_no) {
                    abort(403, '支付号不存在');
                }
            }

            // 防止重复支付
            if (!is_null($pay_no->pay_time)) {
                abort(403, '该支付号已被支付过了');
            }

            if ($pay_no->pay_type == 'custom_deposit') {
                $custom_log = $pay_no->custom_log;
                if (!$custom_log) {
                    abort(403, '支付号已失效，请联系客服');
                }
            }

            // 生成交易数据进行支付
            if ($driver == 'alipay') {
                $trade = [
                    'out_trade_no' => (string) $pay_no->pay_no,
                    'total_amount' => $pay_no->pay_amount,
                    'subject'      => '步履 - 支付号' . $pay_no->pay_no,
                    // 花呗分期 - 余额，余额宝，花呗，花呗分期，借记卡
                    // 'enable_pay_channels' => 'pcredit,balance,moneyFund,debitCardExpress,pcreditpayInstallment,bankPay',
                    'enable_pay_channels' => 'balance,moneyFund,debitCardExpress,credit_group,bankPay',
                ];
                // // 判断支付类型为订单时，计算订单总价（不用了，notify是在根据类型处理）
                // if ($pay_no->pay_type == 'order') {
                //     $trade = [
                //         'out_trade_no' => $pay_no->pay_no,
                //         'total_amount' => $pay_no->pay_amount,
                //         'subject'      => '步履 - 支付号' . $pay_no->pay_no,
                //         //花呗分期
                //         'enable_pay_channels' => 'balance,moneyFund,pcredit,pcreditpayInstallment,debitCardExpress,bankPay',
                //     ];
                // } elseif ($pay_no->pay_type == 'custom_deposit') {
                //     $trade = [
                //         'out_trade_no' => $pay_no->pay_no,
                //         'total_amount' => $pay_no->pay_amount,
                //         'subject'      => '步履 - 支付号' . $pay_no->pay_no,
                //         //花呗分期
                //         'enable_pay_channels' => 'balance,moneyFund,pcredit,pcreditpayInstallment,debitCardExpress,bankPay',
                //     ];
                // }

                // 返回支付信息
                $res['data']['pay_info'] = Pay::alipay()->$method($trade)->getContent();
            } else {
                $trade = [
                    'out_trade_no'  => (string) $pay_no->pay_no . "_{$pay_no->pc_code}",
                    'total_fee'     => bcmul($pay_no->pay_amount, 100),
                    'body'          => '步履 - 支付号' . $pay_no->pay_no,
                ];

                if (in_array($method, ['mp', 'miniapp'])) {
                    $trade['openid'] = Request::input('openid');
                }

                if ($method == 'app') {
                    // 返回支付信息
                    $res['data']['pay_info'] = Pay::wechat()
                                                  ->switchConfig(config('pay.wechat_app'))
                                                  ->app($trade)
                                                  ->getContent();
                } elseif ($method == 'miniapp') {
                    // 返回支付信息
                    $res['data']['pay_info'] = Pay::wechat()
                                                  ->switchMiniapp(config('pay.wechat.miniapp_id_list.'. Request::input('miniapp_name')))
                                                  ->miniapp($trade)
                                                  ->toArray();
                } else {
                    // 返回支付信息
                    $res['data']['pay_info'] = Pay::wechat()->$method($trade)->getContent();
                }
            }

            // 更新支付号的支付工具代码
            $pay_no->payment_code = implode('_', [$driver, $method]);
            $pay_no->save();

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
            // $res['trace'] = $e->getTrace();
        }

        return $res;
    }
}
