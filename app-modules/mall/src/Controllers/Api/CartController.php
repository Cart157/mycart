<?php

namespace Modules\Mall\Controllers\Api;

use Modules\Mall\Models;
use Request;
use Validator;
use JWTAuth;

class CartController extends \BaseController
{
    /**
     * 列出用户的购物车商品，支持多店铺
     */
    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            // 通过token获取user_id
            $user_id = JWTAuth::user()->id;

            // 按照加入购物车的最新时间，取得所有店铺
            $store_id_arr = Models\Cart::where('user_id', $user_id)
                                       ->orderBy('updated_at', 'desc')
                                       ->pluck('store_id')
                                       ->unique()
                                       ->toArray();

            // 按店铺顺序，按加入先后，取得购物车中的商品
            $store_id_str = implode(',', $store_id_arr);
            $cart_items   = Models\Cart::where('user_id', $user_id)
                                       //->has('goods')  删除商品也显示，但库存为null
                                       ->with('goods')
                                       ->orderByRaw("instr(',{$store_id_str},', CONCAT(',',store_id,','))")
                                       //instr(',1,2,3,', CONCAT(',',store_id,','))')  真正格式
                                       //->orderByRaw('instr(\',' . implode(',', $stores) . ',\', CONCAT(\',\',store_id,\',\'))')
                                       ->orderBy('updated_at', 'desc')
                                       ->get();

            $res['data']['cart_items'] = $cart_items->each(function($item) {
                //$item->goods_spec       = json_decode($item->goods_spec, true);
		// TODO: 验证
                $item->goods_spec;
                $item->stock            = $item->goods ? $item->goods->stock : null;
                $item->current_price    = $item->goods ? $item->goods->goods_price : null;
                unset($item->goods);
            });

            // 取得店铺数量
            $res['data']['store_cnt']  = count($store_id_arr);

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 添加商品至的购物车，支持统一商品的叠加
     */
    public function store()
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'goods_num'     => 'required|numeric',
            'goods_id'      => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 验证购买数量和库存
            if (Request::input('goods_num') == 0) {
                abort(400, '购买数量不能为0');
            }

            $get_goods = Models\Goods::with('spu')->find(Request::input('goods_id'));

            if (!$get_goods || $get_goods->stock < Request::input('goods_num')) {
                abort(400, '库存不足，请重新选择数量');
            }

            // 通过token获取user_id
            $user_id = JWTAuth::user()->id;

            // 通过user_id找同款现货
            $cart = Models\Cart::where('goods_id', $get_goods->id)
                               ->where('user_id', $user_id)
                               ->first();

            // 加入购物车
            if ($cart) {
                $cart->goods_num       += Request::input('goods_num');
            } else {
                $cart = new Models\Cart();
                $cart->user_id          = $user_id;
                $cart->store_id         = $get_goods->spu->store_id;
                $cart->goods_id         = $get_goods->id;
                $cart->goods_name       = $get_goods->spu->name;
                $cart->goods_price      = $get_goods->goods_price;
                $cart->goods_num        = Request::input('goods_num');
                $cart->goods_image      = $get_goods->cover_image;
/*
                // 处理规格
                $spec_tmp = json_decode($get_goods->sku_spec, true);
                $goods_spec = [];
                foreach ($spec_tmp as $spec) {
                    $spec_value = Models\SpecValue::find($spec['value']);
                    $goods_spec[] = [
                        'spec_name'         => $spec['spec_name'],
                        'spec_value_name'   => $spec_value->name,
                    ];
                }
                $cart->goods_spec       = json_encode($goods_spec, JSON_UNESCAPED_UNICODE);
 */
                $spec_tmp = json_decode($get_goods->sku_spec, true);
                foreach ($spec_tmp as $spec) {
                    $spec_value = Models\SpecValue::find($spec['value']);
                    if (isset($goods_spec)) {
                        $goods_spec = $goods_spec.'  ';
                    } else {
                        $goods_spec = '';
                    }
                    $goods_spec = $goods_spec.$spec['spec_name'].'：'.$spec_value->name;
                }
                $cart->goods_spec = $goods_spec;
            }

            $cart->save();

            $res['data']['cart_count'] = Models\Cart::where('user_id', $user_id)
                                                    //->has('goods')  删除的商品也显示
                                                    ->count();

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 更新购物车的商品，只支持改变商品数量
     */
    public function batchUpdate()
    {
        $res = parent::apiDeletedResponse();

        $validator = Validator::make(Request::all(), [
            'cart_item_change' => 'required|array',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 通过token获取user_id
            $user_id = JWTAuth::user()->id;

            // 取得改动的item_id
            $item_id_arr = array_keys(Request::input('cart_item_change'));

            // 检查要删除的是否都是自己购物车里的
            $has_illegal =  Models\Cart::whereIn('id', $item_id_arr)
                                       ->where('user_id', '<>', $user_id)
                                       ->count();

            if ($has_illegal) {
                abort(500, '非法操作，不能更新');
            } else {
                Models\Cart::destroy(Request::input('item_id'));
                foreach (Request::input('cart_item_change') as $item_id => $goods_num) {
                    $cart_item = Models\Cart::find($item_id);
                    if ($cart_item) {
                        $cart_item->goods_num = $goods_num;
                        $cart_item->save();
                    }
                }
            }

        } catch(Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function batchDestroy()
    {
        $res = parent::apiDeletedResponse();

        $validator = Validator::make(Request::all(), [
            'cart_item_id'      => 'required|array',
            'cart_item_id.*'    => 'integer',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 通过token获取user_id
            $user_id = JWTAuth::user()->id;

            // 检查要删除的是否都是自己购物车里的
            $has_illegal =  Models\Cart::whereIn('id', Request::input('cart_item_id'))
                                       ->where('user_id', '<>', $user_id)
                                       ->count();

            if ($has_illegal) {
                abort(500, '非法操作，不能删除');
            } else {
                Models\Cart::destroy(Request::input('cart_item_id'));
            }

        } catch(Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 取得购物车商品数量
     * @url get:mall/cart/count?token=通过登录获取的token值
     */
    public function count()
    {
        $res = parent::apiFetchedResponse();

        try {
            // 通过token获取user_id
            $user_id = JWTAuth::user()->id;

            $res['data']['cart_count'] = Models\Cart::where('user_id', $user_id)
                                                    //->has('goods')  删除的商品也显示
                                                    ->count();

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 结算购物车，检查库存并未下单
     * @url post:api/mall/cart/checkout
     */
    public function checkout()
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'goods_id'          => 'integer',
            'goods_num'         => 'integer',
            'cart_item_id'      => 'array',
            'cart_item_id.*'    => 'integer',
        ]);

        // 非购物车的情况，必须有商品ID
        $validator->sometimes(['goods_id', 'goods_num'], 'required', function () {
            return empty(Request::input('cart_item_id'));
        });

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 通过token获取user_id
            $user_id = JWTAuth::user()->id;

            // 购物车订单
            if (Request::has('cart_item_id')) {
                // 检查要删除的是否都是自己购物车里的
                $has_illegal =  Models\Cart::whereIn('id', Request::input('cart_item_id'))
                                           ->where('user_id', '<>', $user_id)
                                           ->count();

                if ($has_illegal) {
                    abort(403, '非法操作:尝试结算其他用户的购物车商品');
                } else {
                    // 验证要结算商品的有效性
                    $vaild_goods = Models\Cart::whereIn('id', Request::input('cart_item_id'))
                                              ->orderBy('updated_at', 'desc')
                                              ->has('goods')
                                              ->with('goods')
                                              ->get();

                    if (count($vaild_goods) < count(Request::input('cart_item_id'))) {
                        abort(403, '有商品刚被下架，请重新选择要结算的商品');
                    }

                    // 验证库存
                    foreach ($vaild_goods as $cart_item) {
                        if ($cart_item->goods_num > $cart_item->goods->stock) {
                            $msg = sprintf('%s 现在库存为 %d，请重选数量'
                                , $cart_item->goods_name
                                , $cart_item->goods->stock
                            );
                            abort(403, $msg);
                        }
                    }
                }

                $checkout_goods = $vaild_goods;

            // 立即购买订单
            } else {
                $get_goods = Models\Goods::find(Request::input('goods_id'));

                if (!$get_goods) {
                    abort(403, '商品刚被下架，请重新选择商品');
                }

                if (Request::input('goods_num') > $get_goods->stock) {
                    $msg = sprintf('%s 现在库存为 %d，请重选数量'
                        , $get_goods->name
                        , $get_goods->stock
                    );
                    abort(403, $msg);
                }

                // 伪造一个购物车项目
                $cart_item = new Models\Cart();
                $cart_item->user_id         = $user_id;
                $cart_item->store_id        = $get_goods->spu->store_id;
                $cart_item->goods_id        = $get_goods->id;
                $cart_item->goods_name      = $get_goods->spu->name;
                $cart_item->goods_price     = $get_goods->goods_price;
                $cart_item->goods_num       = Request::input('goods_num');
                $cart_item->goods_image     = $get_goods->cover_image;

                // 处理规格
                $spec_tmp = json_decode($get_goods->sku_spec, true);
                $goods_spec = [];
                foreach ($spec_tmp as $spec) {
                    $spec_value = Models\SpecValue::find($spec['value']);
                    $goods_spec[] = [
                        'spec_name'         => $spec['spec_name'],
                        'spec_value_name'   => $spec_value->name,
                    ];
                }
                $cart_item->goods_spec      = json_encode($goods_spec, JSON_UNESCAPED_UNICODE);

                // 伪造:goods关系模型
                $cart_item->goods           = $get_goods;
                // 转成集合
                $checkout_goods = collect([$cart_item]);
            }

            // 计算运费和商品价
            $goods_amount = 0;
            $freight_arr  = [];
            foreach ($checkout_goods as $item) {
                $item->current_price = $item->goods->goods_price;

                $goods_amount       += $item->current_price * $item->goods_num;
                $freight_arr        += array_fill(count($freight_arr), $item->goods_num, $item->goods->freight);
            }

            // 计算运费（首个全额运费，其余运费减半）
            rsort($freight_arr);
            $shift_freight = array_shift($freight_arr);
            $total_freight = $shift_freight + array_sum($freight_arr) / 2;

            $res['data']['goods_amount']  = $goods_amount;
            $res['data']['total_freight'] = $total_freight;

            // XXX:这块前端可以用index里的数据
            // // 按店铺顺序，按加入先后，取得要结算的商品
            // $store_id_arr = Models\Cart::where('user_id', $user_id)
            //                            ->orderBy('updated_at', 'desc')
            //                            ->pluck('store_id')
            //                            ->unique()
            //                            ->toArray();

            // $store_id_str = implode(',', $store_id_arr);
            // $cart_items = Models\Cart::where('user_id', $user_id)
            //                          ->whereIn('id', Request::input('cart_item_id'))
            //                          ->with('goods')
            //                          ->orderByRaw("instr(',{$store_id_str},', CONCAT(',',store_id,','))")
            //                          ->orderBy('updated_at', 'desc')
            //                          ->get();

            // $res['data']['cart_items'] = $cart_items->each(function($item) {
            //     $item->stock = $item->goods ? $item->goods->stock : null;
            //     $item->current_price = $item->goods ? $item->goods->goods_price : null;
            //     unset($item->goods);
            // });

            // // 取得店铺数量
            // $res['data']['store_cnt'] = count($store_id_arr);

        } catch (Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
