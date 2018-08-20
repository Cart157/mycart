<?php

namespace Modules\Oms\Controllers\Api;

use Modules\Oms\Models;
use Modules\Base\Models as BaseModels;
use Modules\Mall\Models as MallModels;
use Modules\Activity\Models as ActivityModels;
use Modules\Care\Models as CareModels;
use Modules\Customization\Models as CustomizationModels;
use Request;
use Validator;
use DB;
use JWTAuth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class OrderController extends \BaseController
{
    /**
     * 订单列表--排除定制的和洗护的订单
     * @url get:/api/oms/order?token=token值
     */
    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            $condition = Request::all();
            # 定制师能查询到的
            $newOrder = new Models\Order();

            if(request()->has('seller_id')) {
                $condition['seller_id'] = JWTAuth::user()->id;
            } else {
                $condition['user_id']   = JWTAuth::user()->id;
                # 普通用户排除定制订单
                $newOrder = $newOrder->whereNotIn('order_type', [20, 21, 22, 23, 30]);
            }

            $res['data'] = $newOrder
                ->search($condition)
                ->each(function ($item) {
                foreach ($item->goods as $key => $goods) {
                    unset($item->goods[$key]->id);
                    unset($item->goods[$key]->order_id);
                    unset($item->goods[$key]->goods_amount);
                    unset($item->goods[$key]->created_at);
                    unset($item->goods[$key]->updated_at);
                    unset($item->goods[$key]->deleted_at);
                }

                // $item->order_items = json_decode($item->order_items, true);
            });

        } catch (\Exception $e) {
            // dd($e->getTrace());
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 订单详情
     * @URL get:/api/oms/order?token=登陆后的token值
     */
    public function show($id)
    {
        //初始化返回信息$res = [code=200 message=""]
        $res = parent::apiFetchedResponse();

//        dd( $id );

        try {
            //查询制定订单号的订单详情
            // $order = Models\Order::select('id', 'pay_no', 'user_id', 'order_no', 'waybill_no', 'order_status', 'evaluation_status', 'refund_status', 'refund_record', 'goods_amount', 'freight', 'order_amount', 'created_at', 'deliver_at', 'finished_at')->findOrFail($id);
            $order = Models\Order::select('id', 'pay_no', 'user_id', 'order_no', 'store_name', 'waybill_no', 'order_type', 'order_status', 'order_status_custom', 'evaluation_status', 'refund_status', 'refund_record', 'goods_amount', 'freight', 'express_type', 'insurance_fee', 'insurance_amount', 'order_amount', 'created_at', 'deliver_at', 'finished_at')->findOrFail($id);

            //如果查询的订单的用户和当前用户不同 则报错

            #-iamnner  用户的身份： 当前用户id==订单中的makeid就是定制师 当前用户id==订单中的userid就是定制用户  否则就是平台
            # $custom_log = CustomizationModels\UserCustomLog::find($id);
            $user_id = JWTAuth::user()->id;

            $platformUser = BaseModels\PlatformUser::where('user_id', $user_id)->first();

            if ($order->user_id != $user_id && !$platformUser) {
                 return error_json(403, "您无权查看！");
            }
            #==============================================

            if ($order->payNo) {
                //订单时间
                $order->payment_time = $order->payNo->pay_time;
//                dd( $order->expansion );
                unset($order->pay_no, $order->payNo);
            }

            if( $order->expansion ){
                //扩展表信息
                $order->consignee_name = $order->expansion->consignee_name;
                $consignee_info = json_decode($order->expansion->consignee_info, true);
                $order->consignee_mobile = $consignee_info['mb_phone'];
                $order->consignee_address = $consignee_info['area_info'] . ' ' . $consignee_info['address'];

                //unset掉无用的变量 节约内存
                unset($order->user_id, $order->expansion);
            }

            # 新增 20180702 - 查找修复的内容
            if( 30 == $order->order_type ){
                if ($order->careLog) {
                    $order->careLog = $order->careLog;
                    if ($order->careLog->goods) {
                        $order->careLog->goods->buy_item        = json_decode($order->careLog->goods->buy_item, true);
                        $order->careLog->goods->left_image      = json_decode($order->careLog->goods->left_image, true);
                    }
                }
            }
            # end 新增 20180702 - 查找修复的内容

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

            // $order->order_items = json_decode($order->order_items, true);

            $res['data'] = $order;
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 提交订单
     * 1.验证提交信息
     * 2.提交订单
     */
    public function store()
    {
        $res = parent::apiCreatedResponse();

        // if (Request::has('goods_id') && Request::has('cart_item_id')) {
        //     abort(403, '非法操作');
        // }

        // 不报403了，强行排他
        if (Request::has('custom_design')) {
            // 如果是私人定制，unset其他
            Request::offsetUnset('goods_id');
            Request::offsetUnset('goods_num');
            Request::offsetUnset('cart_item_id');
            Request::offsetUnset('care_log_id');
        } elseif (Request::has('care_log_id')) {
            // 如果是洗护，unset掉其他
            Request::offsetUnset('goods_id');
            Request::offsetUnset('goods_num');
            Request::offsetUnset('cart_item_id');
            Request::offsetUnset('custom_log_id');
        } elseif (Request::has('custom_log_id')) {
            // 如果是自己提供原鞋，unset掉购物车
            Request::offsetUnset('cart_item_id');
        } elseif (Request::has('cart_item_id')) {
            // 如果是购物车，unset掉单独购买
            Request::offsetUnset('goods_id');
            Request::offsetUnset('goods_num');
        }

        $validator = Validator::make(Request::all(), [
            'goods_id'          => 'integer',
            'goods_num'         => 'integer',
            'cart_item_id'      => 'array',
            'cart_item_id.*'    => 'integer',
            'consignee_id'      => 'integer',
            'express_type'      => 'required|string',
            'insurance_amount'  => 'integer',
            'user_coupon_id'    => 'array',
            'user_coupon_id.*'  => 'integer',
            'user_coins'        => 'integer',
            'order_remark'      => 'max:300',
            'custom_log_id'     => 'integer',   // 对应 ext_user_custom_log 表
            'custom_design'     => 'digits:1',
            'care_log_id'       => 'integer',
            'only_calculate'    => 'digits:1',
        ]);

        // 非购物车和私人定制的情况，必须有商品ID
        $validator->sometimes(['goods_id', 'goods_num'], 'required', function () {
            return empty(Request::input('cart_item_id')) && !Request::has('custom_design') && !Request::has('care_log_id');
        });

        // 如果是私人定制，就必须有 custom_log_id
        $validator->sometimes(['custom_log_id'], 'required', function () {
            // XXX:此处也可以用 required_if
            return Request::has('custom_design');
        });

        $validator->sometimes(['consignee_id'], 'required', function () {
            return !Request::has('only_calculate');
        });

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 通过token获取user_id
            $user    = JWTAuth::user();
            $user_id = JWTAuth::user()->id;

            // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            // 1. 业务验证
            // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            // 1.0 验证快递方式
            $logistics_express = config('const.logistics_express');
            if (!isset($logistics_express[Request::input('express_type')])) {
                abort(403, '快递方式有误，请重新选择');

                // TODO:提交订单前还有二次验证（验证是否能使用包邮和到付）
            }

            // 1.1 取得收货人信息（并验证）
            if (!Request::input('only_calculate')) {
                $consignee = Models\Consignee::find(Request::input('consignee_id'));
                if (!$consignee || $consignee->user_id != $user_id) {
                    abort(403, '收货地址不存在，请重新选择');
                }
            }

            $promotion_info = [];
            // 1.2 取得优惠券信息（并验证）
            if (Request::input('user_coupon_id')) {
                // 检查coupon有效性（是否属于该user_id,是否使用过或已过期）
                $known_type = [];
                $user_coupons = [];
                foreach (Request::input('user_coupon_id') as $user_coupon_id) {
                    $coupon = ActivityModels\Promotion\UserCoupon::where('is_used', 0)
                                                       ->find($user_coupon_id);

                    if (!$coupon || $coupon->user_id != $user_id) {
                        abort(403, '您使用了不存在或已使用的优惠券');
                    }

                    if ($coupon->start_time > Carbon::now() || $coupon->end_time <= Carbon::now()) {
                        abort(403, "“{$coupon->name}”该券不在有效期内，请重新选择");
                    }

                    if (!in_array($coupon->type_name, $known_type)) {
                        $known_type[] = $coupon->type_name;
                    } else {
                        abort(403, "“{$coupon->type_name}”只能使用一张，请重新选择");
                    }

                    // TODO:提交订单前还有二次验证（下面有实现）

                    $user_coupons[] = $coupon;
                }

                $promotion_info['coupons'] = $user_coupons;
            }

            // 1.3 取得金币使用量（并验证）
            if (Request::input('user_coins')) {
                if (Request::input('user_coins') % 1000 > 0) {
                    abort(403, '金币只能使用 1000 的倍数个');
                }

                if (Request::input('user_coins') > $user->profile->coin_num) {
                    abort(403, '使用的金币大于了账户所拥有的');
                }

                // TODO:提交订单前还有二次验证（下面有实现）

                $promotion_info['coins'] = Request::input('user_coins');
            }

            // 1.4 取得并绑定鞋子信息（并验证）
            if (Request::input('custom_log_id')) {
                // 取出log
                $custom_log = CustomizationModels\UserCustomLog::find(Request::input('custom_log_id'));
                if (!$custom_log) {
                    abort(400, '缺少必要的鞋子信息');
                }

                // 验证这个需求是否属于提交订单者本人
                if ($custom_log->user_id != $user_id) {
                    abort(403, '鞋子信息数据异常');
                }

                // TODO:提交订单前还有二次验证（下面有实现）
            }

            // 1.5 取得并绑定洗护鞋子信息（并验证）
            if (Request::input('care_log_id')) {
                // 取出log
                $care_log = CareModels\CareOrderLog::find(Request::input('care_log_id'));
                if (!$care_log) {
                    abort(400, '缺少必要的鞋子信息');
                }

                // 验证这个需求是否属于提交订单者本人
                if ($care_log->user_id != $user_id) {
                    abort(403, '鞋子信息数据异常');
                }

                if ($care_log->status != 22) {
                    abort(403, '洗护需求状态异常，还不能提交订单');
                }

                $order_type = 30;
                $goods_amount = $care_log->price;
                if (Request::input('insurance_amount') > $goods_amount) {
                    abort(403, '保价金额不能超过商品价值 ￥'. $goods_amount);
                }
            }

            // 2.循环生成支付号，查重
            $pay_no = BaseModels\PayNo::makePayNo($user_id);

            // 3.生成订单
            // 3.1如果是私人定制，直接生成定制订单
            if (Request::input('custom_design')) {
                // 二次验证鞋子信息
                if ($custom_log->status != 23) {
                    abort(403, '定制需求还没最终确定方案，不能提交订单');
                }

                $goods_amount = $custom_log->price;
                if (Request::input('insurance_amount') > $goods_amount) {
                    abort(403, '保价金额不能超过商品价值 ￥'. $goods_amount);
                }

                // 二次验证优惠券，是否专属能用，是否达到满减要求
                if (isset($promotion_info['coupon'])) {
                    foreach ($promotion_info['coupon'] as $coupon) {
                        // 1)验证:专属优惠券还要看订单类型
                        $scope = $coupon->type_scope;

                        if ($scope == 'freight') {
                            // 验证快递方式 如果包邮或到付不让他使用
                            $express_type = Request::input('express_type');
                            if ($express_type == 'sf_daofu' || $express_type == 'yd_baoyou') {
                                abort(403, "您选择了包邮或到付，请不要使用运费券");
                            }
                        } elseif ($scope != 'custom') {
                            abort(403, "“{$coupon->name}” 不是定制券，请重新选择");
                        }

                        // 2)验证:是否符合满减要求
                        if ($goods_amount < $coupon->rule_over) {
                            abort(403, "“{$coupon->name}” 未达到满减要求，请重新选择");
                        }
                    }
                }

                // 二次验证使用的金币数
                if (isset($promotion_info['coin'])) {
                    $valid_coins = Models\Order::validCoins($goods_amount, $user->profile->coin_num);
                    if ($promotion_info['coupon'] > $valid_coins) {
                        abort(403, "金币最多可抵商品价的 5%，您本次最多可使用 {$can_use} 个金币");
                    }
                }

                // 伪造一个购物车项目
                $cart_item = new MallModels\Cart();
                $cart_item->goods_price = $goods_amount;
                $cart_item->goods_num   = 1;

                // 伪造:goods关系模型
                // $cart_item->goods = $get_goods; //此处不伪造，用于判断是私人定制的集合

                // 转成集合
                $checkout_goods = collect([$cart_item]);

                if (Request::input('only_calculate')) {
                    $calc_ret = Models\Order::calcOrderAmount($checkout_goods, [
                        'express_type'      => Request::input('express_type'),
                        'insurance_amount'  => Request::input('insurance_amount'),
                        'custom_log'        => $custom_log,
                        'goods_amount'      => $goods_amount,
                        'promotion_info'    => $promotion_info,
                        'order_type'        => 22,
                        'only_calculate'    => true,
                    ]);
                } else {
                    //生成私人定制订单
                    $order_id = Models\Order::makeCustomOrder($user, $checkout_goods, [
                        'pay_no'            => $pay_no,
                        'order_remark'      => Request::input('order_remark'),
                        'consignee'         => $consignee,
                        'express_type'      => Request::input('express_type'),
                        'insurance_amount'  => Request::input('insurance_amount'),
                        'custom_log'        => $custom_log,
                        'goods_amount'      => $goods_amount,
                        'promotion_info'    => $promotion_info,
                        'order_type'        => 22,
                    ]);
                }

            // 3.2如果是洗护，直接生成定制订单
            } elseif (Request::input('care_log_id')) {
                // 二次验证优惠券，是否专属能用，是否达到满减要求
                if (isset($promotion_info['coupon'])) {
                    foreach ($promotion_info['coupon'] as $coupon) {
                        // 1)验证:专属优惠券还要看订单类型
                        $scope = $coupon->type_scope;

                        if ($scope == 'freight') {
                            // 验证快递方式 如果包邮或到付不让他使用
                            $express_type = Request::input('express_type');
                            if ($express_type == 'sf_daofu' || $express_type == 'yd_baoyou') {
                                abort(403, "您选择了包邮或到付，请不要使用运费券");
                            }
                        } elseif ($scope != 'care') {
                            abort(403, "“{$coupon->name}” 不是洗护券，请重新选择");
                        }

                        // 2)验证:是否符合满减要求
                        if ($goods_amount < $coupon->rule_over) {
                            abort(403, "“{$coupon->name}” 未达到满减要求，请重新选择");
                        }
                    }
                }

                // 二次验证使用的金币数
                if (isset($promotion_info['coin'])) {
                    $valid_coins = Models\Order::validCoins($goods_amount, $user->profile->coin_num);
                    if ($promotion_info['coupon'] > $valid_coins) {
                        abort(403, "金币最多可抵商品价的 5%，您本次最多可使用 {$can_use} 个金币");
                    }
                }

                // 伪造一个购物车项目
                $cart_item = new MallModels\Cart();
                $cart_item->goods_price = $goods_amount;
                $cart_item->goods_num   = 1;

                // 伪造:goods关系模型
                // $cart_item->goods = $get_goods; //此处不伪造，用于判断是洗护的集合

                // 转成集合
                $checkout_goods = collect([$cart_item]);

                if (Request::input('only_calculate')) {
                    $calc_ret = Models\Order::calcOrderAmount($checkout_goods, [
                        'express_type'      => Request::input('express_type'),
                        'insurance_amount'  => Request::input('insurance_amount'),
                        'care_log'          => $care_log,
                        'goods_amount'      => $goods_amount,
                        'promotion_info'    => $promotion_info,
                        'order_type'        => 30,
                        'only_calculate'    => true,
                    ]);
                } else {
                    //生成定制订单-imanner
                    $order_id = Models\Order::makeCareOrder($user, $checkout_goods, [
                        'pay_no'            => $pay_no,
                        'order_remark'      => Request::input('order_remark'),
                        'consignee'         => $consignee,
                        'express_type'      => Request::input('express_type'),
                        'insurance_amount'  => Request::input('insurance_amount'),
                        'care_log'          => $care_log,
                        'goods_amount'      => $goods_amount,
                        'promotion_info'    => $promotion_info,
                        'order_type'        => 30,
                    ]);
                }

            // 3.3否则就是购物车或直接购买的（包括其他非私人定制的定制）
            } else {
                // 1)通过购物车结算的订单
                if (Request::input('cart_item_id')) {
                    $has_illegal = MallModels\Cart::whereIn('id', Request::input('cart_item_id'))
                                              ->where('user_id', '<>', $user_id)
                                              ->count();

                    if ($has_illegal) {
                        abort(403, '要结算的商品异常，其中包含了其他用户购物车里的商品');
                    }

                    $checkout_goods = MallModels\Cart::whereIn('id', Request::input('cart_item_id'))
                                                 ->orderBy('updated_at', 'desc')
                                                 ->with('goods')
                                                 ->get();

                // 2)通过商品立即购买的订单
                } else {
                    $get_goods = MallModels\Goods::find(Request::input('goods_id'));

                    //如果没有获取到商品
                    if (!$get_goods) {
                        abort(403, '商品刚被下架，请重新选择商品');
                    }

                    // 伪造一个购物车项目
                    $cart_item = new MallModels\Cart();
                    $cart_item->user_id     = $user_id;
                    $cart_item->store_id    = $get_goods->spu->store_id;
                    $cart_item->goods_id    = $get_goods->id;
                    $cart_item->goods_name  = $get_goods->spu->name;
                    $cart_item->goods_price = $get_goods->goods_price;
                    $cart_item->goods_num   = Request::input('goods_num');
                    $cart_item->goods_image = $get_goods->cover_image;

                    // 整理商品的规格（因为购物车项有这个，生产订单时会用到）
                    $spec_tmp = json_decode($get_goods->sku_spec, true);
                    foreach ($spec_tmp as $spec) {
                        $spec_value = MallModels\SpecValue::find($spec['value']);
                        if (isset($goods_spec)) {
                            $goods_spec = $goods_spec . '  ';
                        } else {
                            $goods_spec = '';
                        }
                        $goods_spec = $goods_spec . $spec['spec_name'] . '：' . $spec_value->name;
                    }
                    $cart_item->goods_spec = $goods_spec;

                    // 伪造:goods关系模型
                    $cart_item->goods = $get_goods;

                    // 转成集合
                    $checkout_goods = collect([$cart_item]);
                }

                // 3)验证库存，下架，计算商品总额和订单类型
                $goods_amount = 0;
                foreach ($checkout_goods as $cart_item) {
                    // 这个验证主要是购物车结算时用的
                    if (!$cart_item->goods) {
                        $msg = sprintf('“%s” 已被下架，请重新选择'
                            , $cart_item->goods_name
                        );
                        abort(403, $msg);
                    }

                    // 验证购买数量
                    if ($cart_item->goods_num < 1) {
                        $msg = sprintf('“%s” 购买个数必须大于等于1件，请重选数量'
                            , $cart_item->goods_name
                        );
                        abort(403, $msg);
                    }

                    // 验证库存
                    if ($cart_item->goods_num > $cart_item->goods->stock) {
                        $msg = sprintf('“%s” 现在库存为 %d，请重选数量'
                            , $cart_item->goods_name
                            , $cart_item->goods->stock
                        );
                        abort(403, $msg);
                    }

                    // 计算商品总额
                    $goods_amount = bcadd($goods_amount, $cart_item->goods->goods_price * $cart_item->goods_num, 2);

                    // 计算订单类型
                    $order_type = $cart_item->goods->service_type;
                    if ($order_type != 10) {
                        if (count($checkout_goods) > 1) {
                            abort(403, "特殊商品（洗护&定制）不能使用购物车");
                        }
                    }

                    if ($order_type == 30) {
                        $store_name = 'BAN洗护团队';
                    } elseif ($order_type >= 20) {
                        $store_name = 'BAN定制团队';
                    } else {
                        $store_name = 'BAN官方自营';
                    }
                }

                if (Request::input('insurance_amount') > $goods_amount) {
                    abort(403, '保价金额不能超过商品价值 ￥'. $goods_amount);
                }

                // 4)二次验证
                // 二次验证优惠券，是否专属能用，是否达到满减要求
                if (isset($promotion_info['coupons'])) {
                    foreach ($promotion_info['coupons'] as $coupon) {
                        // 验证:专属优惠券还要看订单类型
                        $scope = $coupon->type_scope;

                        // 运费券
                        if ($scope == 'freight') {
                            // 什么也不做，通过
                            // 或者验证 快递方式 如果包邮或到付不让他使用
                            $express_type = Request::input('express_type');
                            if ($express_type == 'sf_daofu' || $express_type == 'yd_baoyou') {
                                abort(403, "您选择了包邮或到付，请不要使用运费券");
                            }

                        // 洗护券
                        } elseif ($order_type >= 30) {
                            if ($scope != 'care' || $scope != 'care') {
                                abort(403, "“{$coupon->name}” 不是洗护券或运费券，请重新选择");
                            }

                        // 定制券
                        } elseif ($order_type >= 20) {
                            if ($scope != 'custom' || $scope != 'care') {
                                abort(403, "“{$coupon->name}” 不是定制券或运费券，请重新选择");
                            }

                        // 普通商品券
                        } else {
                            if ($scope != 'goods' || $scope != 'care') {
                                abort(403, "“{$coupon->name}” 不是商品券或运费券，请重新选择");
                            }
                        }

                        // 验证:是否符合满减要求
                        if ($goods_amount < $coupon->rule_over) {
                            abort(403, "“{$coupon->name}” 未达到满减要求，请重新选择");
                        }
                    }
                }

                // 二次验证使用的金币数
                if (isset($promotion_info['coins'])) {
                    $valid_coins = Models\Order::validCoins($goods_amount, $user->profile->coin_num);
                    if ($promotion_info['coins'] > $valid_coins) {
                        abort(403, "金币最多可抵商品价的 5%，您本次最多可使用 {$can_use} 个金币");
                    }
                }

                // 二次验证鞋子信息
                if (isset($custom_log)) {
                    if ($checkout_goods->first()->goods_num > 1) {
                        abort(403, "自己提供原鞋的商品一次只能拍1双");
                    }
                } elseif ($order_type == 21) {
                    abort(400, '自己提供原鞋的订单，必须填写鞋子的信息');
                }

                if (Request::input('only_calculate')) {
                    $calc_ret = Models\Order::calcOrderAmount($checkout_goods, [
                        'express_type'      => Request::input('express_type'),
                        'insurance_amount'  => Request::input('insurance_amount'),
                        'goods_amount'      => $goods_amount,
                        'promotion_info'    => $promotion_info,
                        'only_calculate'    => true,
                        'order_type'    => $order_type,
                    ]);
                } else {
                    // 5)提交订单
                    $order_id = Models\Order::makeOrder($user, $checkout_goods, [
                        'pay_no'        => $pay_no,
                        'order_remark'  => Request::input('order_remark'),
                        'store_id'      => $checkout_goods->first()->store_id,
                        'store_name'    => $store_name,
                        'consignee'     => $consignee,
                        'custom_log'    => Request::has('custom_log_id') ? $custom_log : null,
                        'express_type'      => Request::input('express_type'),
                        'insurance_amount'  => Request::input('insurance_amount'),
                        'goods_amount'  => $goods_amount,
                        'promotion_info'=> $promotion_info,
                        'order_type'    => $order_type,
                    ]);
                }

// 这块是原来写的，包含购物车多店同时结算，比较麻烦，所以重写了，暂时购物车不做多店了
//                 // 购物车订单
//                 if (Request::has('cart_item_id')) {
//                     // 检查要结算的是否都是自己购物车里的
//                     $has_illegal = Models\Cart::whereIn('id', Request::input('cart_item_id'))
//                         ->where('user_id', '<>', $user_id)
//                         ->count();

//                     if ($has_illegal) {
//                         abort(403, '非法操作:尝试结算其他用户的购物车商品');
//                     } else {
//                         // 验证要结算商品的有效性
//                         $vaild_goods = Models\Cart::whereIn('id', Request::input('cart_item_id'))
//                             ->orderBy('updated_at', 'desc')
//                             ->has('goods')
//                             ->with('goods')
//                             ->get();

//                         if (count($vaild_goods) < count(Request::input('cart_item_id'))) {
//                             abort(403, '有商品刚被下架，请重新选择要结算的商品');
//                         }

//                         // 验证库存
//                         foreach ($vaild_goods as $cart_item) {
//                             if ($cart_item->goods_num > $cart_item->goods->stock) {
//                                 $msg = sprintf('%s 现在库存为 %d，请重选数量'
//                                     , $cart_item->goods_name
//                                     , $cart_item->goods->stock
//                                 );
//                                 abort(403, $msg);
//                             }
//                         }
//                     }

//                     $store_id_arr = Models\Cart::where('user_id', $user_id)
//                         ->orderBy('updated_at', 'desc')
//                         ->pluck('store_id')
//                         ->unique()
//                         ->toArray();

//                     // 单店 hack: store_id默认为1
//                     $order_info[1] = [
//                         'order_remark' => Request::input('order_remark'),
//                         'custom_log' => Request::has('custom_log_id') ? $custom_log : null,
//                         'coupon' => Request::has('my_coupon_id') ? $coupon : null,
//                     ];

//                     // 开是生成订单了（多店铺，每个店铺生成一个订单）
//                     foreach ($store_id_arr as $store_id) {
//                         // 取得指定店铺的要结算商品
//                         $checkout_goods = $vaild_goods->where('store_id', $store_id);

//                         $order_id = Models\Order::makeOrder($checkout_goods, [
//                             'pay_no' => $pay_no,
//                             'order_remark' => $order_info[$store_id]['order_remark'],
//                             'store_id' => $store_id,
//                             'consignee' => $consignee,
//                             'custom_log' => $order_info[$store_id]['custom_log'],
//                             'coupon' => $order_info[$store_id]['coupon'],
//                             //imanner
// //                            'order_type' => 20,
//                         ]);
//                     }

//                     // 立即购买订单
//                 } else {
//                     // 获取商品信息
//                     $get_goods = Models\Goods::find(Request::input('goods_id'));

//                     # imanenr------------
//                     /*if( $get_goods->service_type == 20 ){
//                         if( $get_goods->service_type_custom == 11 ){
//                             order->order_type = 20;
//                         }elseif( $get_goods->service_type_custom == 12 ){
//                             order->order_type = 23;
//                         }

//                     }
// */
//                     # imanenr

//                     //如果没有获取到商品
//                     if (!$get_goods) {
//                         abort(403, '商品刚被下架，请重新选择商品');
//                     }

//                     //判断商品数量
//                     if (Request::input('goods_num') > $get_goods->stock) {
//                         $msg = sprintf('%s 现在库存为 %d，请重选数量'
//                             , $get_goods->name
//                             , $get_goods->stock
//                         );
//                         abort(403, $msg);
//                     }

//                     // 伪造一个购物车项目
//                     $cart_item = new Models\Cart();
//                     $cart_item->user_id = $user_id;
//                     $cart_item->store_id = $get_goods->spu->store_id;
//                     $cart_item->goods_id = $get_goods->id;
//                     $cart_item->goods_name = $get_goods->spu->name;
//                     $cart_item->goods_price = $get_goods->goods_price;
//                     $cart_item->goods_num = Request::input('goods_num');
//                     $cart_item->goods_image = $get_goods->cover_image;
//                     /*
//                                     // 处理规格
//                                     $spec_tmp = json_decode($get_goods->sku_spec, true);
//                                     $goods_spec = [];
//                                     foreach ($spec_tmp as $spec) {
//                                         $spec_value = Models\SpecValue::find($spec['value']);
//                                         $goods_spec[] = [
//                                             'spec_name'         => $spec['spec_name'],
//                                             'spec_value_name'   => $spec_value->name,
//                                         ];
//                                     }
//                                     $cart_item->goods_spec      = json_encode($goods_spec, JSON_UNESCAPED_UNICODE);
//                     */
//                     $spec_tmp = json_decode($get_goods->sku_spec, true);
//                     foreach ($spec_tmp as $spec) {
//                         $spec_value = Models\SpecValue::find($spec['value']);
//                         if (isset($goods_spec)) {
//                             $goods_spec = $goods_spec . '  ';
//                         } else {
//                             $goods_spec = '';
//                         }
//                         $goods_spec = $goods_spec . $spec['spec_name'] . '：' . $spec_value->name;
//                     }
//                     $cart_item->goods_spec = $goods_spec;

//                     // 伪造:goods关系模型
//                     $cart_item->goods = $get_goods;
//                     // 转成集合
//                     $checkout_goods = collect([$cart_item]);

//                     $order_id = Models\Order::makeOrder($checkout_goods, [
//                         'pay_no' => $pay_no,
//                         'order_remark' => Request::input('order_remark'),
//                         'store_id' => $get_goods->spu->store_id,
//                         'consignee' => $consignee,
//                         'custom_log' => Request::has('custom_log_id') ? $custom_log : null,
//                         'coupon' => Request::has('my_coupon_id') ? $coupon : null,
//                     ]);
//                 }
            }

            if (Request::input('only_calculate')) {
                $res['data'] = $calc_ret;
            } else {
                $res['data']['order_id'] = $order_id;
                $res['data']['pay_no'] = $pay_no;
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
            $res['trace'] = $e->getTrace();
        }

        return $res;
    }

    //修改订单：取消订单（仅待支付状态可取消）、确认收货（仅已发货状态可确认）
    public function update($id)
    {
        $res = parent::apiCreatedResponse();

        //$array = ['order_status' => 0];

        $validator = Validator::make(Request::all(), [
            'order_status' => [
                Rule::in([0, 40]),
            ],
        ]);

        try {
            if ($validator->fails()) {
                abort(500, '非法操作，不能更新');
            }
            $user_id = JWTAuth::user()->id;

            //检查订单是否是该用户的
            $rs_order = Models\Order::where('id', $id)->where('user_id', $user_id)->first();
            if ($rs_order) {
                //检查取消订单的状态是否为10，检查确认收货订单状态是否为30
                if (Request::input('order_status') == 0) {
                    if ($rs_order->order_status == 10) {
                        $rs_order->order_status = 0;
                        $rs_order->finished_at = date("Y-m-d H:i:s");
                    } else {
                        abort(500, '非法操作，不能更新bb');
                    }
                } else {
                    if ($rs_order->order_status == 30) {
                        $rs_order->order_status = 40;
                        $rs_order->finished_at = date("Y-m-d H:i:s");
                    } else {
                        abort(500, '非法操作，不能更新aa');
                    }
                }
            } else {
                abort(500, '非法操作，不能更新');
            }
            $rs_order->save();
        } catch (Exception $e) {
            $res = parent::apiException($e, $res);
        }
        return $res;
    }



    /**
     *  删除订单/取消删除订单--软删除
     * @param int order_id 订单id
     * @return mixed
     * @url delete:/api/oms/order/{id}
     */
    public function destroy($id)
    {
        /**
         * 基本逻辑：
         *  接受参数：订单id【oeder表的id】
         *  验证层：
         *      首先要根据订单号查询数据
         *      1，验证订单是否已找到
         *      2，判断当前用户和查询数据中的user_id是否一致
         *      3，订单状态必须是：0,40
         *      4，判断订单没有已经删除过
         *  数据删除
         *      删除order表及其相关的几张表
         */
        $res = parent::apiCreatedResponse();

        # 开启事务
        DB::beginTransaction();

        try {
            if ($validator->fails()) {
                return error_json(403, "缺少订单号" );
            }
            # 获取当前的用户id
            $user_id = JWTAuth::user()->id;

            # 检查订单是否是该用户的
            $rs_order = Models\Order::withTrashed()
                ->with('expansion','goods','customLog','flows')
                ->where('id', $id)
                ->where('user_id', $user_id)
                ->first();

            # 如果没有在表中查询到数据
            if( !$rs_order ){
                return error_json( 404, "没有找到该订单信息" );
            }
            # 如果查询到的订单用户和登录用户不统一
            if( $rs_order->user_id != $user_id ){
                return error_json( 403, "这不是您的订单，你无权删除" );
            }
            # 检查订单状态只能是0【取消订单】40【完成订单】
            if( !in_array( $rs_order->order_status, [0,40] ) ){
                return error_json( 403, "只有已完成和取消的订单可删除" );
            }
            # 如果这条记录的delete_at 字段不为空 表示已经软删除了
            if( $rs_order->deleted_at ){
                return error_json(403, "该订单已删除，请不要重复操作");
            }

            # 3，数据操作
            # 删除扩展信息
            if($rs_order->expansion){
                $rs_order->expansion()->delete();
            }
            # 删除订单商品
            if($rs_order->goods){
                $rs_order->goods()->delete();
            }
            # 删除订单追踪
            if($rs_order->flows){
                $rs_order->flows()->delete();
            }
            # 删除订单定制详情
            if($rs_order->items){
                $rs_order->items()->delete();
            }

            # 软删除 rs_order
            $rs_order->delete();
            if( !$rs_order->trashed() ){
                throw new \Exception("主订单删除失败");
            }
        } catch (Exception $e) {
            DB::rollBack();
            $res = parent::apiException($e, $res);
        }
        DB::commit();
        return $res;
    }

    /**
     * 订单修改
     * @param $id 订单号
     * @return mixed
     */
    public function updateWaybillNo($id)
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            //谁提供的信息
            'from'              => 'required|string|in:user,platform,servicer',
            //运单号
            'waybill_no'        => 'required|numeric',
            // //物流公司
            // 'logistics_company' => 'required|string|max:10',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $user_id = JWTAuth::user()->id;
            //找到订单
            $order = Models\Order::find($id);
            if (!$order) {
                abort(403, '订单不存在');
            }

            if ($order->order_type < 20) {
                abort(403, '订单类型有误');
            }

            if ($order->order_status != 20) {
                abort(403, '订单状态有误');
            }

            // 普通用户的相关操作
            if (Request::input('from') == 'user') {
                if ($order->order_type != 21 && $order->order_type != 22 && $order->order_type != 30) {
                    abort(403, '订单类型有误');
                }

                if ($user_id != $order->user_id) {
                    abort(403, '这不是您的订单');
                }

                // 这个状态才是等待邮往平台
                if ($order->order_status_custom != 0) {
                    abort(403, '订单状态有误');
                }

                $order->waybill_no_custom = json_encode([
                    'user' => [
                        'waybill_no'        => Request::input('waybill_no'),
                        // 'logistics_company' => Request::input('logistics_company'),
                    ],
                ]);

                order_flow([
                    'order_id' => $order->id,
                    'op_user_id' => $user_id,
                    'op_log' => '您的鞋子正在邮往平台',
                    'op_info' => [
                        'type' => 'express_user_platform',
                        'waybill_no' => Request::input('waybill_no'),
                    ],
                ]);

                // 修改订单状态
                $order->order_status_custom = 1;

            // 平台相关的操作
            } elseif (Request::input('from') == 'platform') {
                if ($order->order_type != 21 && $order->order_type != 22 && $order->order_type != 30) {
                    abort(403, '订单类型有误');
                }

                // 如果用户不是平台人员
                $exists = BaseModels\PlatformUser::where('user_id', $user_id)
                                                 ->where('is_used', 1)
                                                 ->first();
                if (!$exists) {
                    abort(403, '您不是平台工作人员');
                }

                // 这个状态才是等待邮往服务商
                if ($order->order_status_custom != 23) {
                    abort(403, '订单状态有误');
                }

                $waybill_data = json_decode($order->waybill_no_custom, true);

                $waybill_data['platform'] = [
                    'waybill_no' => Request::input('waybill_no'),
                    // 'logistics_company' => Request::input('logistics_company'),
                ];

                $order->waybill_no_custom = json_encode($waybill_data);

                order_flow([
                    'order_id' => $order->id,
                    'op_user_id' => $user_id,
                    'op_log' => '正在发往定制师',
                    'op_info' => [
                        'type' => 'express_platform_servicer',
                        'waybill_no' => Request::input('waybill_no'),
                    ],
                ]);

                // 修改订单状态
                $order->order_status_custom = 3;

            // 服务商的相关操作
            } else {
                // 如果用户是平台人员
                $is_platform_user = BaseModels\PlatformUser::where('user_id', $user_id)
                                                           ->where('is_used', 1)
                                                           ->first();

                if ($is_platform_user) {
                    if ($order->order_type != 21 && $order->order_type != 22 && $order->order_type != 30) {
                        abort(403, '您只能填写部分定制和洗护的服务商至用户的快递单号');
                    }

                    if ($order->order_status_custom != 21) {
                        abort(403, '订单当前状态还不允许填写这里的快递单号');
                    }
                } else {
                    if ($order->order_type != 20 && $order->order_type != 23) {
                        abort(403, '您无权填写此类型订单的快递单号');
                    }

                    if ($user_id != $order->seller_id) {
                        abort(403, '您无权该订单的快递单号');
                    }

                    // 上面限制了类型这里就不用限制自定义状态了
                    // if ($order->order_status_custom > 0) {
                    //     abort(403, '订单当前状态不允许填写这里的快递单号');
                    // }
                }

                $order->waybill_no = Request::input('waybill_no');

                order_flow([
                    'order_id' => $order->id,
                    'op_user_id' => $user_id,
                    'op_log' => '您的订单已发出，预计XXX日送达您的手中',
                    'op_info' => [
                        'type' => 'express_servicer_user',
                        'waybill_no' => Request::input('waybill_no'),
                    ],
                    # 这个不显示
                    'is_show' => $is_platform_user ? 0 : 1,
                ]);

                if ($is_platform_user) {
                    $order->order_status_custom = 23;
                } else {
                    $order->order_status = 30;
                }
            }

            $order->save();

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 订单状态跟踪
     * @param $id 订单的id 例如170
     * @return mixed
     */
    public function flows($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            $user_id = JWTAuth::user()->id;

            #-iamnner  用户的身份： 当前用户id==订单中的makeid就是定制师 当前用户id==订单中的userid就是定制用户  否则就是平台
            # $custom_log = CustomizationModels\UserCustomLog::find($id);
            $custom_log = CustomizationModels\UserCustomLog::where('order_id', $id)->first();
            $platformUser = BaseModels\PlatformUser::where('user_id', $user_id)->count();


            if (!$custom_log) {
                return error_json(400, '缺少必要的鞋子信息');;
                // $custom_log->order_id = $order_id;
                // $custom_log->save();
            }

            if($user_id == $custom_log->user_id || $user_id == $custom_log->maker_id || $platformUser){
                if( $user_id == $custom_log->user_id ){
                    # 普通用户
                    $user_status = $platformUser ? 5 : 1;

                }elseif( $user_id == $custom_log->maker_id ){
                    # 定制师
                    $user_status = $platformUser ? 6 : 2;

                }elseif($platformUser){
                    # 平台
                    $user_status = 4;
                }
            }else{
                return error_json(403, "您无权查看该订单！");
            }

            $res['user_status'] = $user_status;
            #==============================================

            $order = Models\Order::find($id);

            if (!$order) {
                abort(403, '订单不存在');
            }

            /*$res['data'] = $order->flows->where('display', 1)->each(function ($item) {
                $item->op_info = json_decode($item->op_info, true);
            });*/
            $res['data'] = Models\OrderFlow::where('order_id', $id )
                ->where('is_show', 1)
                ->orderBy('created_at', 'desc')
                ->get()
                ->each(function ($item) {
                    $item->op_info = json_decode($item->op_info, true);
                 });

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
            $res['trace'] = $e->getTrace();
        }

        return $res;
    }

    /**
     * 返回订单结算时所需的各项数据
     */
    public function checkout()
    {
        $res = parent::apiCreatedResponse();

        // if (Request::has('goods_id') && Request::has('cart_item_id')) {
        //     abort(403, '非法操作');
        // }

        // 不报403了，强行排他
        if (Request::has('custom_design')) {
            // 如果是私人定制，unset其他
            Request::offsetUnset('goods_id');
            Request::offsetUnset('goods_num');
            Request::offsetUnset('cart_item_id');
            Request::offsetUnset('care_log_id');
        } elseif (Request::has('care_log_id')) {
            // 如果是洗护，unset掉其他
            Request::offsetUnset('goods_id');
            Request::offsetUnset('goods_num');
            Request::offsetUnset('cart_item_id');
            Request::offsetUnset('custom_log_id');
        } elseif (Request::has('custom_log_id')) {
            // 如果是自己提供原鞋，unset掉购物车
            Request::offsetUnset('cart_item_id');
        } elseif (Request::has('cart_item_id')) {
            // 如果是购物车，unset掉单独购买
            Request::offsetUnset('goods_id');
            Request::offsetUnset('goods_num');
        }

        $validator = Validator::make(Request::all(), [
            'goods_id'          => 'integer',
            'goods_num'         => 'integer',
            'cart_item_id'      => 'array',
            'cart_item_id.*'    => 'integer',
            'custom_log_id'     => 'integer',
            'custom_design'     => 'digits:1',
            'care_log_id'       => 'integer',
        ]);

        // 非购物车和私人定制的情况，必须有商品ID
        $validator->sometimes(['goods_id', 'goods_num'], 'required', function () {
            return empty(Request::input('cart_item_id')) && !Request::has('custom_design') && !Request::has('care_log_id');
        });

        // 如果是私人定制，就必须有 custom_log_id
        $validator->sometimes(['custom_log_id'], 'required', function () {
            return Request::has('custom_design');
        });

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $user = JWTAuth::user();
            $user_id = $user->id;

            if (Request::input('care_log_id')) {
                // 取出log
                $care_log = CareModels\CareOrderLog::find(Request::input('care_log_id'));
                if (!$care_log) {
                    abort(400, '缺少必要的鞋子信息');
                }

                // 验证这个需求是否属于提交订单者本人
                if ($care_log->user_id != $user_id) {
                    abort(403, '鞋子信息数据异常');
                }
            }

            if (Request::input('custom_log_id')) {
                // 取出log
                $custom_log = CustomizationModels\UserCustomLog::find(Request::input('custom_log_id'));
                if (!$custom_log) {
                    abort(400, '缺少必要的鞋子信息');
                }

                // 验证这个需求是否属于提交订单者本人
                if ($custom_log->user_id != $user_id) {
                    abort(403, '鞋子信息数据异常');
                }
            }

            if (Request::input('custom_design')) {
                if ($custom_log->status != 23) {
                    abort(403, '定制需求状态异常，还不能提交订单');
                }

                $order_type = 22;
                $goods_amount = $custom_log->price;
            } elseif (Request::input('care_log_id')) {
                if ($care_log->status != 22) {
                    abort(403, '洗护需求状态异常，还不能提交订单');
                }

                $order_type = 30;
                $goods_amount = $care_log->price;
            } else {
                // 1.通过购物车结算的订单
                if (Request::has('cart_item_id')) {
                    $has_illegal = MallModels\Cart::whereIn('id', Request::input('cart_item_id'))
                                                ->where('user_id', '<>', $user_id)
                                                ->count();

                    if ($has_illegal) {
                        abort(403, '要结算的商品异常，其中包含了其他用户购物车里的商品');
                    }

                    $checkout_goods = MallModels\Cart::whereIn('id', Request::input('cart_item_id'))
                                                ->orderBy('updated_at', 'desc')
                                                ->with('goods')
                                                ->get();

                // 2.通过商品立即购买的订单
                } else {
                    $get_goods = MallModels\Goods::find(Request::input('goods_id'));

                    //如果没有获取到商品
                    if (!$get_goods) {
                        abort(403, '商品刚被下架，请重新选择商品');
                    }

                    // 伪造一个购物车项目
                    $cart_item = new MallModels\Cart();
                    $cart_item->user_id     = $user_id;
                    $cart_item->store_id    = $get_goods->spu->store_id;
                    $cart_item->goods_id    = $get_goods->id;
                    $cart_item->goods_name  = $get_goods->spu->name;
                    $cart_item->goods_price = $get_goods->goods_price;
                    $cart_item->goods_num   = Request::input('goods_num');
                    $cart_item->goods_image = $get_goods->cover_image;

                    // 伪造:goods关系模型
                    $cart_item->goods = $get_goods;

                    // 转成集合
                    $checkout_goods = collect([$cart_item]);
                }

                // 3.验证库存，下架，计算商品总额和订单类型
                $goods_amount = 0;
                foreach ($checkout_goods as $cart_item) {
                    // 这个验证主要是购物车结算时用的
                    if (!$cart_item->goods) {
                        $msg = sprintf('“%s” 已被下架，请重新选择'
                            , $cart_item->goods_name
                        );
                        abort(403, $msg);
                    }

                    // 验证购买数量
                    if ($cart_item->goods_num < 1) {
                        $msg = sprintf('“%s” 购买个数必须大于等于1件，请重选数量'
                            , $cart_item->goods_name
                        );
                        abort(403, $msg);
                    }

                    // 验证库存
                    if ($cart_item->goods_num > $cart_item->goods->stock) {
                        $msg = sprintf('“%s” 现在库存为 %d，请重选数量'
                            , $cart_item->goods_name
                            , $cart_item->goods->stock
                        );
                        abort(403, $msg);
                    }

                    // 计算商品总额
                    $goods_amount = bcadd($goods_amount, $cart_item->goods->goods_price * $cart_item->goods_num, 2);

                    // 计算订单类型
                    $order_type = $cart_item->goods->service_type;
                    if ($order_type != 10) {
                        if (count($checkout_goods) > 1) {
                            abort(403, "特殊商品（洗护&定制）不能使用购物车");
                        }
                    }
                }

                // 二次验证鞋子信息
                if (isset($custom_log)) {
                    if ($checkout_goods->first()->goods_num > 1) {
                        abort(403, "自己提供原鞋的商品一次只能拍1双");
                    }
                } elseif ($order_type == 21) {
                    abort(400, '自己提供原鞋的订单，必须填写鞋子的信息');
                }
            }

            // 可用的快递
            $res['data']['express']     = Models\Order::validExpress($order_type);
            // 可用的金币
            $res['data']['coins']       = [
                'valid' => Models\Order::validCoins($goods_amount, $user->profile->coin_num),
                'total' => $user->profile->coin_num,
            ];

            // 先取得用户所有的优惠券，然后按减价力度倒序，然后一个个试
            $user_coupons = ActivityModels\Promotion\UserCoupon::where('user_id', $user->id)
                                                     ->where('is_used', 0)
                                                     ->get();

            $valid_coupons = [];
            $invalid_coupons = [];
            foreach ($user_coupons as $coupon) {
                $scope = $coupon->type_scope;

                if ($scope == 'freight') {
                    // 什么也不做，非专属

                // 洗护订单
                } elseif ($order_type == 30) {
                    if ($scope != 'care') {
                        $invalid_coupons[] = $coupon;
                        continue;
                    }

                // 定制订单
                } elseif ($order_type >= 20) {
                    if ($scope != 'custom') {
                        $invalid_coupons[] = $coupon;
                        continue;
                    }

                // 普通商品
                } elseif ($order_type == 10) {
                    if ($scope != 'goods') {
                        $invalid_coupons[] = $coupon;
                        continue;
                    }
                }

                if ($coupon->start_time > Carbon::now() || $coupon->end_time <= Carbon::now()) {
                    $invalid_coupons[] = $coupon;
                    continue;
                } elseif ($goods_amount < $coupon->rule_over) {
                    $invalid_coupons[] = $coupon;
                    continue;
                }

                $valid_coupons[] = $coupon;
            }

            // 3.可用的优惠券
            $res['data']['coupons']['valid']    = $valid_coupons;
            $res['data']['coupons']['invalid']  = $invalid_coupons;

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 提交订单前，每次更改快递，保价，优惠券，金币都会计算一下
     */
    public function calculate()
    {
        // hack一下提交订单，为了不让一个接口返回两套结构的数据
        Request::merge(['only_calculate' => 1]);

        $res = $this->store();

        if (empty($res['data']['promotion_info'])) {
            $res['data']['promotion_info'] = (object) [];
        }

        return $res;
    }

    /**
     * 取得平台的收货人地址
     */
    public function platformConsignee($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            $consignee = Models\Setting::where('key', 'platform_consignee')->first();
            if (!$consignee) {
                abort(404, '没找到平台收货地址，请联系客服');
            }

            $res['data'] = json_decode($consignee->value, true);

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 取得订单服务商的收货人地址
     */
    public function servicerConsignee($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            $user_id = JWTAuth::user()->id;
            $is_platform_user = BaseModels\PlatformUser::where('user_id', $user_id)
                                                       ->where('is_used', 1)
                                                       ->first();

            if (!$is_platform_user) {
                abort(403, '您没有权限取得服务商收货地址');
            }

            $order = Models\Order::find($id);
            if (!$order) {
                abort(404, '该订单不存在');
            }

            if (!$order->seller_id) {
                abort(404, '该订单没有服务商，如觉异常请检查数据');
            }

            $consignee = Models\Consignee::where('user_id', $order->seller_id)
                                         ->where('is_valid', 1)
                                         ->orderBy('is_default', 'desc')
                                         ->first();
            if (!$consignee) {
                abort(404, '该服务商，还未填写收货地址');
            }

            $res['data'] = [
                'consignee_name'    => $consignee->consignee_name,
                'consignee_address' => $consignee->area_info . $consignee->address,
                'consignee_mobile'  => $consignee->mb_phone,
            ];

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
