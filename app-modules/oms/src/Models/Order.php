<?php

namespace Modules\Oms\Models;

use Modules\Base\Models as BaseModels;
use Modules\Customization\Models as CustomizationModels;
use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Order extends \BaseModel
{
    use CrudTrait;
    use SoftDeletes;

    const LIMIT_PER_PAGE = 10;

    protected $table = 'oms_order';
    protected $fillable = ['order_type', 'goods_amount', 'freight', 'order_amount', 'logistics_company_id', 'waybill_no', 'order_status', 'deliver_at', 'finished_at'];


    // ========================================
    // for 关系
    // ========================================
    public function user()
    {
        return $this->belongsTo('Modules\Base\Models\User');
    }

    public function seller()
    {
        return $this->belongsTo('Modules\Base\Models\User', 'seller_id');
    }

    public function goods()
    {
        return $this->hasMany('Modules\Oms\Models\OrderGoods');
    }

    public function flows()
    {
        return $this->hasMany('Modules\Oms\Models\OrderFlow')->orderBy('id', 'desc');
    }

    public function expansion()
    {
        return $this->hasOne('Modules\Oms\Models\OrderExpansion', 'order_id');
    }

    public function refund()
    {
        return $this->hasOne('Modules\Oms\Models\Refund', 'order_id');
    }

    public function payNo()
    {
        return $this->belongsTo('Modules\Base\Models\PayNo', 'pay_no', 'pay_no');
    }

    public function logistics()
    {
        return $this->belongsTo('Modules\Oms\Models\Logistics', 'logistics_company_id');
    }

    //订单与订制需求1对1关系
    public function customLog()
    {
        return $this->hasOne('Modules\Customization\Models\UserCustomLog','order_id');
    }

    // //订单与修复订单1对1关系
    public function careLog()
    {
        return $this->hasOne('Modules\Care\Models\CareOrderLog','order_id');
    }


    // ========================================
    // for 访问器 & 修改器
    // ========================================
    // 反序列化 order_extra
    public function getOrderExtraAttribute($value)
    {
        return json_decode($value, true);
    }


    // ========================================
    // for other
    // ========================================
    public function poolbuyPool()
    {
        if (class_exists('Modules\Activity\Models\Promotion\PoolbuyMember')) {
            $member = \Modules\Activity\Models\Promotion\PoolbuyMember::where('order_id', $this->id)->first();

            if ($member) {
                return $member->pool;
            } else {
                return null;
            }
        }

        return null;
    }


    // ========================================
    // for search
    // ========================================
    /**
     * 详情搜索
     * @param Builder $q ORM对象
     * @param array $condition  条件数组
     * @param array $fields 要搜索的字段
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    // public function scopeSearch(Builder $q, Array $condition, Array $fields = ['id', 'order_no', 'goods_amount', 'freight', 'order_amount', 'order_type', 'order_status', 'order_status_custom', 'order_items', 'evaluation_status', 'refund_status', 'refund_record','waybill_no'])
    public function scopeSearch(Builder $q, Array $condition, Array $fields = [])
    {
        // order_status
        // evaluation_status
        // wd
        // limit
        // page
        $select_fields = ['id', 'order_no', 'goods_amount', 'freight', 'order_amount', 'order_type', 'order_status', 'order_status_custom', 'order_items', 'evaluation_status', 'refund_status', 'refund_record','waybill_no'];

        if ($fields) {
            $select_fields = array_merge($select_fields, $fields);
        }

        $q->select($select_fields);

        if (isset($condition['wd'])) {
            $test = $condition['wd'];
            $q->whereHas('goods', function($query) use($condition) {
                $query->where('goods_name', 'like', '%'.$condition['wd'].'%');
            });
        }

        //判断是用户还是卖家
        if (isset($condition['user_id'])) {
            $q->where('user_id', $condition['user_id']);
        } elseif (isset($condition['seller_id'])) {
            $q->where('seller_id', $condition['seller_id']);
        }

        //待处理
        if (isset($condition['handing'])) {
            $q->where(function ($query) {
                $query->whereIn('order_status', [1,10,30])
                    ->whereIn('order_type', [20,23]);
            });
            $q->orWhere(function ($query) {
                $query->whereIn('order_status', [1,20,10,30])
                    ->whereIn('order_type', [21,22])
                    ->whereIn('order_status_custom',[0,1,2,3,21,22,23]);

            });
        }

        //          "order_type": 20,
        //            "order_status": 30,
        //            "order_status_custom": 0,

        //制作中订单
        if (isset($condition['making'])) {

            $q->Where(function ($query) {
                $query->whereIn('order_type', [20,23])
                    ->where('order_status', 20);
            });
            $q->orWhere(function ($query) {
                $query->whereIn('order_type', [21,22])
                    ->where('order_status', 20)
                    ->whereIn('order_status_custom', [4,5]);
            });
        }
        //完成
        if (isset($condition['complete'])) {

//            $q->Where(function ($query) {
//                $query->whereIn('order_type', [20,23])
//                    ->whereIn('order_status', [40,0]);
//            });
//            $q->orWhere(function ($query) {
//                $query->whereIn('order_type', [21,22])
//                    ->whereIn('order_status', [40,0]);
//            });
            $q->whereIn('order_status', [50,40,0]);
        }

        //待付款：order_status=10
        //待发货：order_status=20
        //待收货：order_status=30
        if (isset($condition['order_status']) && isset($condition['seller_id'])) {
            $q->whereIn('order_status', $condition['order_status']);
        }elseif (isset($condition['order_status']) )
        {
            $q->where('order_status', $condition['order_status']);
        }

        //待评价：order_status=40&evaluation_status=0
        if (isset($condition['evaluation_status'])) {
            $q->where('evaluation_status', $condition['evaluation_status']);
        }

        //订制订单
        if (isset($condition['order_status_custom'])) {
            $q->whereIn('order_status_custom', $condition['order_status_custom']);
        }


        $take_num = self::LIMIT_PER_PAGE;
        if (isset($condition['limit'])) {
            $take_num = (int) $condition['limit'];
            $q->take($take_num);
        }

        if (isset($condition['page'])) {
            $skip_num = $take_num * ($condition['page'] - 1);
            $q->skip($skip_num)
              ->take($take_num);
        }

        return $q->orderBy('created_at', 'desc')->get();
    }


    // ========================================
    // for make
    // ========================================
    // 生成订单（想改成 makeCartOrder）
    public static function makeOrder($user, $cart_items, $order_option)
    {
        $order_type = $order_option['order_type'];
        if ($order_type == 22 || $order_type == 30) {
            abort(403, '普通订单提交，不支持私人定制和洗护');
        }

        // 事务开始
        DB::beginTransaction();

        try {
            // 1.创建支付号
            $pay_no = BaseModels\PayNo::create([
                'user_id'   => $user->id,
                'pay_no'    => $order_option['pay_no'],
            ]);

            // 2.生成订单号
            $order_no = self::makeOrderNo($pay_no->id, $order_type);

            // 3.生成订单
            $order = new Order();
            $order->order_no    = $order_no;
            $order->pay_no      = $pay_no->pay_no;
            $order->user_id     = $user->id;
            $order->user_name   = $user->name;
            $order->store_id    = $order_option['store_id'];
            $order->store_name  = $order_option['store_name'];
            $order->order_type  = $order_type;
            $order->order_from  = 2; // 让app传过来自己是android还是ios
            $order->order_status= 10; // 待付款/订单生成

            $order_expansion = new OrderExpansion();
            $order_option['consignee']->setVisible(['area_code', 'area_info', 'address', 'mb_phone', 'tel_phone', 'zip_code']);
            $order_expansion->consignee_name    = $order_option['consignee']->consignee_name;
            $order_expansion->consignee_info    = $order_option['consignee']->toJson(JSON_UNESCAPED_UNICODE);
            $order_expansion->order_remark      = $order_option['order_remark'];

            $order->save();
            $order->expansion()->save($order_expansion);

            // 3.1绑定自己提供原鞋的收集信息
            // XXX:最好是验证 $order_option['custom_log'] 的类型（其实也不用）
            if (isset($order_option['custom_log']) && !is_null($order_option['custom_log'])) {
                $order_option['custom_log']->order_id = $order->id;
                $order_option['custom_log']->save();
            }

            // 3.2计算商品价，订单总价，优惠的金额，运费等等
            $calc_ret = self::calcOrderAmount($cart_items, $order_option);
            $order->goods_amount    = $calc_ret['goods_amount'];
            $order->order_amount    = $calc_ret['order_amount'];
            $order->freight         = bcadd($calc_ret['freight'], $calc_ret['insurance_fee'], 2);
            $order->express_type    = $calc_ret['express_type'];
            $order->insurance_fee   = $calc_ret['insurance_fee'];
            $order->insurance_amount= $calc_ret['insurance_amount'];

            // 3.3 定制商品(不含私人定制)
            if ($order_type >= 20 && $order_type < 30) {
                // 生成 custom_log
                CustomizationModels\UserCustomLog::create([
                    'user_id'       => $user->id,
                    'order'         => $order->id,
                    'shoe_image'    => [],  // db not null
                    'shoe_size'     => 0,   // db not null
                ]);

                $first_goods = $cart_items->first()->goods;
                $order->seller_id = $first_goods->seller_id;

                $goods_spu           = $first_goods->spu;

                // 通过spu找到作品发补贴，然后得到定制周期
                $custom_post = CustomizationModels\CustomPost::where('goods_spu_id', $goods_spu->id)->first();

                if (!$custom_post) {
                    abort(403, '定制作品不存在，请联系客服');
                }

                $order_items_type = [20 => 'custom_stocks', 21 => 'custom_diy', 23 => 'custom_futures'];
                $goods_extra       = [
                    'type'      => $order_items_type[$order_type],
                    'maker_name'=> $first_goods->seller->name,
                    'cycle'     => $custom_post->custom_time,
                ];

                if ($order_type == 21) {
                    $goods_extra['item_id'] = $order_option['custom_log']->id;
                }

                // $order->order_items  = json_encode($goods_extra, JSON_UNESCAPED_UNICODE);
                $calc_ret['order_goods_arr'][0]->goods_extra = json_encode($goods_extra, JSON_UNESCAPED_UNICODE);
            }

            // 3.4保存订单
            $order->save();
            $order->goods()->saveMany($calc_ret['order_goods_arr']);

            $order_expansion->promotion_info    = json_encode($calc_ret['promotion_info'], JSON_UNESCAPED_UNICODE);
            $order_expansion->deduct_amount     = $calc_ret['deduct_amount'];
            $order_expansion->save();

            $pay_no->pay_amount = $calc_ret['order_amount'];
            $pay_no->save();

            // 4.作废扣减使用过的东西
            // 4.1作废优惠券
            if (isset($order_option['promotion_info']['coupons'])) {
                foreach ($order_option['promotion_info']['coupons'] as $coupon) {
                    $coupon->is_used = 1;
                    $coupon->save();
                }
            }

            // 4.2扣除金币
            if (isset($order_option['promotion_info']['coins'])) {
                // 金币扣除
                $user->profile->coin_num = $user->profile->coin_num - $order_option['promotion_info']['coins'];

                // 生成log
                BaseModels\UserCoinLog::create([
                    'user_id'   => $user->id,
                    'change_num'=> -1 * $order_option['promotion_info']['coins'],
                    'use_way_id'=> 5,
                ]);
            }

            // 4.3删除购物车（购物车项个数为 1 且没有 id 的是立即购买）
            if (count($cart_items) == 1 && is_null($cart_items->first()->id)) {
                // 单商品立即购买，不删购物车
            } else {
                $del_cart_ids = $cart_items->pluck('id')->all();
                Cart::destroy($del_cart_ids);
            }

            // 5.生成订单流
            order_flow([
                'order_id'  => ($order_type == 30) ? $order->careLog->id : $order->id,
                'op_log'    => '系统已成功生成订单',
                'sort_order' => 10,
                'op_info'       => [
                    'type'        => 'waitting-user-express',
                ],
            ]);

            // 事务提交
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $order->id;
    }

    // 生成私人定制的订单
    public static function makeCustomOrder($user, $cart_items, $order_option)
    {
        $order_type = $order_option['order_type'];
        if ($order_type != 22) {
            abort(403, '私人定制订单，不支持普通商品');
        }

        // 事务开始
        DB::beginTransaction();

        try {
            // 1.创建支付号
            $pay_no = BaseModels\PayNo::create([
                'user_id'   => $user->id,
                'pay_no'    => $order_option['pay_no'],
            ]);

            // 2.生成订单号
            $order_no = self::makeOrderNo($pay_no->id, $order_type);

            // 3.生成订单
            $order = new Order();
            $order->order_no     = $order_no;
            $order->pay_no       = $pay_no->pay_no;
            $order->user_id      = $user->id;
            $order->user_name    = $user->name;
            $order->store_id     = isset($order_option['store_id']) ? $order_option['store_id'] : 1;
            $order->store_name   = 'BAN定制团队'; // FIXME:改成通过store_id获取的
            $order->seller_id    = $order_option['custom_log']->plan->maker_id;
            $order->order_type   = $order_type;
            $order->order_from   = 2; // 让app传过来自己是android还是ios
            $order->order_status = 10; // 待付款/订单生成

            // $order->freight      = 0;
            $order->deposit      = $order_option['custom_log']->deposit;
            // $order->goods_amount = $order_option['custom_log']->price;
            // $order->order_amount = 0;

            $order_expansion = new OrderExpansion();
            $order_option['consignee']->setVisible(['area_code', 'area_info', 'address', 'mb_phone', 'tel_phone', 'zip_code']);
            $order_expansion->consignee_name    = $order_option['consignee']->consignee_name;
            $order_expansion->consignee_info    = $order_option['consignee']->toJson(JSON_UNESCAPED_UNICODE);
            $order_expansion->order_remark      = $order_option['order_remark'];

            $order->save();
            $order->expansion()->save($order_expansion);

            // 3.1绑定收集信息
            $order_option['custom_log']->order_id = $order->id;
            $order_option['custom_log']->status   = 30; // 已经提交订单
            $order_option['custom_log']->save();

            // 3.2计算商品价，订单总价，优惠的金额，运费等等
            $calc_ret = self::calcOrderAmount($cart_items, $order_option);
            $order->goods_amount    = $calc_ret['goods_amount'];
            $order->order_amount    = $calc_ret['order_amount'];
            $order->freight         = bcadd($calc_ret['freight'], $calc_ret['insurance_fee'], 2);
            $order->express_type    = $calc_ret['express_type'];
            $order->insurance_fee   = $calc_ret['insurance_fee'];
            $order->insurance_amount= $calc_ret['insurance_amount'];

            // 3.3二次保存订单
            $order->save();
            $order->goods()->saveMany($calc_ret['order_goods_arr']);

            $order_expansion->promotion_info    = json_encode($calc_ret['promotion_info'], JSON_UNESCAPED_UNICODE);
            $order_expansion->deduct_amount     = $calc_ret['deduct_amount'];
            $order_expansion->save();

            $pay_no->pay_amount = $calc_ret['order_amount'] - $order->deposit;
            $pay_no->save();

            // 4.作废扣减使用过的东西
            // 4.1作废优惠券
            if (isset($order_option['promotion_info']['coupons'])) {
                foreach ($order_option['promotion_info']['coupons'] as $coupon) {
                    $coupon->is_used = 1;
                    $coupon->save();
                }
            }

            // 4.2扣除金币
            if (isset($order_option['promotion_info']['coins'])) {
                // 金币扣除
                $user->profile->coin_num = $user->profile->coin_num - $order_option['promotion_info']['coins'];

                // 生成log
                BaseModels\UserCoinLog::create([
                    'user_id'   => $user->id,
                    'change_num'=> -1 * $order_option['promotion_info']['coins'],
                    'use_way_id'=> 5,
                ]);
            }

            // 5.生成订单流
            order_flow([
                'order_id'  => $order->id,
                'op_log'    => '系统已成功生成订单',
                'sort_order' => 10,
                'op_info'       => [
                    'type'        => 'waitting-user-express'
                ],
            ]);

            // 事务提交
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $order->id;
    }

    // 生成洗护的订单
    public static function makeCareOrder($user, $cart_items, $order_option)
    {
        $order_type = $order_option['order_type'];
        if ($order_type != 30) {
            abort(403, '洗护订单，不支持普通商品');
        }

        // 事务开始
        DB::beginTransaction();

        try {
            // 1.创建支付号
            $pay_no = BaseModels\PayNo::create([
                'user_id'   => $user->id,
                'pay_no'    => $order_option['pay_no'],
            ]);

            // 2.生成订单号
            $order_no = self::makeOrderNo($pay_no->id, $order_type);

            // 3.生成订单
            $order = new Order();
            $order->order_no     = $order_no;
            $order->pay_no       = $pay_no->pay_no;
            $order->user_id      = $user->id;
            $order->user_name    = $user->name;
            $order->store_id     = isset($order_option['store_id']) ? $order_option['store_id'] : 1;
            $order->store_name   = 'BAN洗护团队'; // FIXME:改成通过store_id获取的
            $order->seller_id    = $order_option['care_log']->service_user_id;
            $order->order_type   = $order_type;
            $order->order_from   = 2; // 让app传过来自己是android还是ios
            $order->order_status = 10; // 待付款/订单生成

            // $order->freight      = 0;
            // $order->goods_amount = $order_option['care_log']->price;
            // $order->order_amount = $order_option['care_log']->price;

            $order_expansion = new OrderExpansion();
            $order_option['consignee']->setVisible(['area_code', 'area_info', 'address', 'mb_phone', 'tel_phone', 'zip_code']);
            $order_expansion->consignee_name    = $order_option['consignee']->consignee_name;
            $order_expansion->consignee_info    = $order_option['consignee']->toJson(JSON_UNESCAPED_UNICODE);
            $order_expansion->order_remark      = $order_option['order_remark'];

            $order->save();
            $order->expansion()->save($order_expansion);

            // 3.1绑定收集信息
            $order_option['care_log']->order_id = $order->id;
            $order_option['care_log']->status   = 30; // 已经提交订单
            $order_option['care_log']->save();

            // 3.2计算商品价，订单总价，优惠的金额，运费等等
            $calc_ret = self::calcOrderAmount($cart_items, $order_option);
            $order->goods_amount    = $calc_ret['goods_amount'];
            $order->order_amount    = $calc_ret['order_amount'];
            $order->freight         = bcadd($calc_ret['freight'], $calc_ret['insurance_fee'], 2);
            $order->express_type    = $calc_ret['express_type'];
            $order->insurance_fee   = $calc_ret['insurance_fee'];
            $order->insurance_amount= $calc_ret['insurance_amount'];

            // 3.3二次保存订单
            $order->save();
            $order->goods()->saveMany($calc_ret['order_goods_arr']);

            $order_expansion->promotion_info    = json_encode($calc_ret['promotion_info'], JSON_UNESCAPED_UNICODE);
            $order_expansion->deduct_amount     = $calc_ret['deduct_amount'];
            $order_expansion->save();

            $pay_no->pay_amount = $calc_ret['order_amount'];
            // $pay_no->pay_type   = 'care';

            $pay_no->save();

            // 4.作废扣减使用过的东西
            // 4.1作废优惠券
            if (isset($order_option['promotion_info']['coupons'])) {
                foreach ($order_option['promotion_info']['coupons'] as $coupon) {
                    $coupon->is_used = 1;
                    $coupon->save();
                }
            }

            // 4.2扣除金币
            if (isset($order_option['promotion_info']['coins'])) {
                // 金币扣除
                $user->profile->coin_num = $user->profile->coin_num - $order_option['promotion_info']['coins'];

                // 生成log
                BaseModels\UserCoinLog::create([
                    'user_id'   => $user->id,
                    'change_num'=> -1 * $order_option['promotion_info']['coins'],
                    'use_way_id'=> 5,
                ]);
            }

            // 5.生成订单流
            
            if($order->careLog->order_type != 5){
                order_flow([
                    'order_id'  => $order->careLog->id,
                    'order_type' => 30,
                    'op_log'    => '系统已成功生成订单',
                    'sort_order' => 10,
                    'op_info'       => [
                        'type'        => 'waitting-user-express',
                        'care_log_id' => $order->careLog->id,
                    ],
                ]);
            }

            // 事务提交
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $order->id;
    }

    /**
     * 计算商品价，订单总价，优惠的金额，运费等等
     */
    public static function calcOrderAmount($cart_items, $order_option)
    {
        $logistics_express = config('const.logistics_express');
        $promotion_data    = $order_option['promotion_info'];
        $goods_amount      = $order_option['goods_amount'];

        // 这里购物车商品的数据（订单商品，运费啊，商品总额啊）
        $has_sneaker    = false;
        $freight_arr    = [];
        $order_goods_arr= [];
        foreach ($cart_items as $item) {
            if (!$item->goods) {
                // 这是私人定制，或洗护
                if ($order_option['order_type'] == 30) {
                    foreach ($order_option['care_log']->goods as $care_goods) {
                        $order_goods = new OrderGoods();
                        $order_goods->goods_type    = 'care_log';
                        $order_goods->goods_id      = $order_option['care_log']->id;
                        $order_goods->goods_name    = $care_goods->goods_name;
                        $order_goods->goods_price   = $care_goods->goods_price;
                        $order_goods->goods_num     = 1;
                        $order_goods->goods_amount  = $care_goods->goods_price * $care_goods->goods_num;
                        $order_goods->goods_image   = $care_goods->left_image;
                        $order_goods->goods_spec    = $care_goods->goods_spec;
                        $order_goods->goods_extra   = json_encode([
                            'type'      => 'care',
                            'log_no'    => $order_option['care_log']->log_no,
                            'maker_name'=> $order_option['care_log']->service_user ? $order_option['care_log']->service_user->name : '官方',
                        ], JSON_UNESCAPED_UNICODE);

                        $order_goods_arr[]  = $order_goods;
                    }
                } else {
                    $order_goods = new OrderGoods();
                    $order_goods->goods_type    = 'custom_log';
                    $order_goods->goods_id      = $order_option['custom_log']->id;
                    $order_goods->goods_name    = $order_option['custom_log']->name;
                    $order_goods->goods_price   = $order_option['custom_log']->price;
                    $order_goods->goods_num     = 1;
                    $order_goods->goods_amount  = $order_option['custom_log']->price;
                    $order_goods->goods_image   = $order_option['custom_log']->cover_image;
                    $order_goods->goods_spec    = '私人定制';
                    $order_goods->goods_extra   = json_encode([
                        'type'      => 'custom_design',
                        // 'item_id'   => $order_option['custom_log']->id,
                        // 'name'      => $order_option['custom_log']->name,
                        // 'cover_image' => $order_option['custom_log']->cover_image,
                        // 'price'     => $order_option['custom_log']->price,
                        // 'num'       => 1,
                        'plan_id'   => $order_option['custom_log']->plan_id,
                        'custom_sn' => $order_option['custom_log']->custom_sn,
                        'maker_name'=> $order_option['custom_log']->plan->user->name,
                        'cycle'     => $order_option['custom_log']->plan->cycle,
                    ], JSON_UNESCAPED_UNICODE);
                }

                $has_sneaker    = true;
                $freight_arr    = [$logistics_express[$order_option['express_type']]['fee']];
                break;
            }

            $order_goods = new OrderGoods();
            $order_goods->goods_id      = $item->goods_id;
            $order_goods->goods_name    = $item->goods_name;
            $order_goods->goods_price   = $item->goods->goods_price;
            $order_goods->goods_num     = $item->goods_num;
            $order_goods->goods_amount  = $item->goods->goods_price * $item->goods_num;
            $order_goods->goods_image   = $item->goods_image;
            $order_goods->goods_spec    = $item->goods_spec;

            $order_goods_arr[]  = $order_goods;

            if ($item->goods->type_id == 1) {   // 1就是鞋(sneaker)
                $freight_arr  += array_fill(count($freight_arr), $item->goods_num, $logistics_express[$order_option['express_type']]['fee']);

                $has_sneaker = true;
            }
        }

        // 计算运费
        $freight = 0;
        if ($has_sneaker) {
            // 首个全额运费，其余运费减半，有鞋的情况只算鞋的运费
            // rsort($freight_arr);
            $shift_freight = array_shift($freight_arr);
            $freight = $shift_freight + array_sum($freight_arr) / 2;
        } else {
            $freight = $logistics_express[$order_option['express_type']]['fee'];
        }

        // 计算报费
        $insurance_fee    = 0;
        $insurance_amount = 0;
        if (isset($order_option['insurance_amount'])) {
            // 保价补足1000整数
            if ($order_option['insurance_amount'] % 1000 == 0) {
                $insurance_amount = $order_option['insurance_amount'];
            } else {
                $insurance_amount = ceil($order_option['insurance_amount'] / 1000) * 1000;
            }


            // 3个insurance_fee意思不同，1)真实费用 2）用户报的数额 3)费率
            $insurance_fee = $insurance_amount * $logistics_express[$order_option['express_type']]['insurance_rate'];

            $insurance_fee = ceil($insurance_fee);
        }

        $promotion_info = [];
        $deduct_amount  = 0;
        if ($promotion_data) {
            if (isset($promotion_data['coupons'])) {
                foreach ($promotion_data['coupons'] as $coupon) {
                    if ($coupon->type_scope == 'freight') {
                        $old = $freight;
                        $freight = $freight - $coupon->rule_deduct;
                        $freight = $freight > 0 ? $freight : 0;

                        $promotion_info['coupons'][] = [
                            'promotion_name'    => $coupon->name,
                            'promotion_type'    => $coupon->type_name,
                            'promotion_item_id' => $coupon->id,
                            'promotion_deduct'  => $old - $freight,
                        ];

                        $deduct_amount += $old - $freight;
                    } else {
                        $old = $goods_amount;
                        $goods_amount = $goods_amount - $coupon->rule_deduct;
                        $goods_amount = $goods_amount > 0 ? $goods_amount : 0;

                        $promotion_info['coupons'][] = [
                            'promotion_name'    => $coupon->name,
                            'promotion_type'    => $coupon->type_name,
                            'promotion_item_id' => $coupon->id,
                            'promotion_deduct'  => $old - $goods_amount,
                        ];

                        $deduct_amount += $old - $goods_amount;
                    }
                }
            }

            if (isset($promotion_data['coins'])) {
                $old = $goods_amount;
                $goods_amount = $goods_amount - $promotion_data['coins'] / 100;
                $goods_amount = $goods_amount > 0 ? $goods_amount : 0;

                $promotion_info['coins'] = [
                    'promotion_type'    => '金币抵扣',
                    'promotion_deduct'  => $old - $goods_amount,
                ];

                $deduct_amount += $old - $goods_amount;
            }
        }

        $order_amount = bcadd(bcadd($goods_amount, $freight, 2), $insurance_fee, 2);
        $order_amount = $order_amount ?: 0.01;

        $ret = [
            'order_amount'      => $order_amount,
            'goods_amount'      => $order_option['goods_amount'],
            'freight'           => $freight,
            'express_type'      => $logistics_express[$order_option['express_type']]['name'],
            'insurance_fee'     => $insurance_fee,
            'insurance_amount'  => $insurance_amount,
            'promotion_info'    => $promotion_info,
            'deduct_amount'     => $deduct_amount,
        ];

        // 只计算和私人定制是没有的
        if (!isset($order_option['only_calculate']) && !empty($order_goods_arr)) {
            $ret['order_goods_arr'] = $order_goods_arr;
        }

        return $ret;
    }

    public function getCouponInfo()
    {
        if ($this->expansion) {
            $promotion_amount = $this->expansion->promotion_amount;
            if ($promotion_amount == 0) {
                return sprintf('没有使用优惠券');
            } else {
                return sprintf('抵扣%d元', $promotion_amount);
            }
        } else {
            return 'error';
        }
    }

    /**
     * 生成支付单编号(两位随机 + 从2000-01-01 00:00:00 到现在的秒数+微秒+会员ID%1000)，该值会传给第三方支付接口
     * 长度 =2位 + 10位 + 3位 + 3位  = 18位
     * 1000个会员同一微秒提订单，重复机率为1/100
     * @return string
     */
    public static function makeOrderNo($pay_id, $order_type = 10)
    {
        //记录生成子订单的个数，如果生成多个子订单，该值会累加
        static $idx;
        if (empty($idx)) {
            $idx = 1;
        } else {
            $idx++;
        }

        if ($order_type == 30) {
            $goods_type = 2;    // 洗护
        } if ($order_type >= 20) {
            $goods_type = 1;    // 定制
        } else {
            $goods_type = 3;    // 普通商品
        }

        do {
            // 6位日期，1位商品类型，1位保障类型，2位订单类型，4位随机数，1位索引
            $order_no = sprintf('%d%d%d%02d%05d%d'
                , $data = date('ymd')   // 日期
                , $goods_type           // FIXME:3是商城商品，还有定制和洗护
                , $contract_type = 1    // FIXME:1是7天无理由退还，还有其他很多
                , $order_type = 1       // FIXME:01是普通购买订单，还有退货换货补货
                , $pay_id               // 支付号ID
                , $idx                  // 计数
            );

            $exists = self::where('order_no', $order_no)->first();
        } while ($exists);

        return $order_no;
    }


    /**
     * 筛选订单
     * @param $orderType 订单类型
     */
    public  function FilterOrder( $orderType ){

    }


    /**
     * 计算某种类型的订单的收益
     * 若是定制订单，使用了优惠券，金币。。。的话，定制师的收益是？
     */
    public function gainsOfOrderType( $order_type, $order_status ){
//        select order.order_amount-order.freight+order.deposit from order,custom_log where seller_id = $maker or maker_id = $maker and order_type in (20,21,22,23) and order_status in (20,30);

    }


    /**
     * 计算某个定制师还没有完成的订单的收益
     */
    public function gainsOfSeller( $seller_id ){
        # 查新订单表中现货定制的订单
        $order_id = self::where('seller_id', $seller_id)->get(['id'])->toArray();
        #查询私人订制和个性定制的订单
        $order_custom_id = (new \Modules\Customization\Models\UserCustomLog())->where('maker_id', $seller_id)->get(['order_id'])->toArray();

        # 获取相关的所有的订单的id
        $order_id_arr = array_merge($order_id, $order_custom_id);
        # 获取所有的订单详情
        $ordersList = self::whereIn('id', $order_id_arr)->whereIn('order_type', [20,21,22,23])->whereIn('order_status', [20, 30])->get();

        # 所有为未完成收益的和
        $allgains = 0.0;
        # 把所有的订单遍历一遍：计算所有订单的收益之和
        foreach ($ordersList as $item) {
            # 订单金额 + 定金 - 运费
            $allgains += $item->order_amount + $item->deposit + $item->freight;
        }

        return $allgains;

    }

    public static function validCoins($goods_amount, $user_coins)
    {
        $by_account = floor($user_coins / 1000) * 1000;
        $by_amount  = floor($goods_amount * 0.05 * 100 / 1000) * 1000;

        return $by_account < $by_amount ? $by_account : $by_amount;
    }

    public static function validExpress($order_type)
    {
        $logistics_express = config('const.logistics_express');

        $express_arr = [];
        if ($order_type == 30) {
            $express_arr[] = $logistics_express['yd_baoyou'];
        } else {
            $express_arr[] = $logistics_express['sf_daofu'];
        }

        $express_arr[] = $logistics_express['sf_tehui'];
        $express_arr[] = $logistics_express['sf_kongyun'];

        return $express_arr;
    }
}
