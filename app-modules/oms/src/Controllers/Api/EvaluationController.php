<?php

namespace Modules\Oms\Controllers\Api;

use Modules\Oms\Models;
use Modules\Mall\Models as MallModels;
use Request;
use Validator;
use JWTAuth;
use DB;

class EvaluationController extends \BaseController
{
    const LIMIT_PER_PAGE = 20;

    /**
     * 列出评价
     */
    public function index()
    {
        $res = parent::apiFetchedResponse();

        $validator = Validator::make(Request::all(), [
            'goods_id'    => 'required|integer',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 获取spu_id
            $goods = MallModels\Goods::find(Request::input('goods_id'));
            if ($goods) {
                $spu_id = $goods->spu->id;
            } else {
                abort(400, 'goods_id不存在');
            }

            $evaluations = Models\Evaluation::where('spu_id', $spu_id)
                                            ->with('user')
                                            ->orderBy('created_at', 'desc')
                                            ->paginate(self::LIMIT_PER_PAGE);

            $evaluations->each(function($item) {
                $item->addHidden(['updated_at', 'deleted_at']);
            });

            $res['data'] = $evaluations->items();

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 发布普通商品评价 不能用于定制商品
     */
    public function store()
    {
        $res = parent::apiCreatedResponse();

        // dd(Request::all());
        // 通过token获取user_id
        $user_id = JWTAuth::user()->id;

        $validator = Validator::make(Request::all(), [
            //订单id
            'order_id'      => 'required|integer',
            //eval必须是 二维数组
            'eval'          => 'required|array',
            'eval.*'        => 'array',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            //根据order_id获取订单详情
            $order = Models\Order::find(Request::input('order_id'));
            dd($order);
            //没有订单信息或者 用户的 id和查询到的用户id不同
            if (!$order || $order->user_id != $user_id) {
                abort(403, '非法操作');
            }

            //获取所有的 goodsid 组成一个数组
            $order_goods_ids = $order->goods->pluck('goods_id')->all();
            // dd($order->goods->all());
            //获取eval中的 goods_id 字段
            $eval_goods_ids  = array_column(Request::input('eval'), 'goods_id');
            //对order_goods_ids 和 eval_goods_ids 排序
            sort($order_goods_ids);
            sort($eval_goods_ids);
            //如果 $order_goods_ids != $eval_goods_ids 则
            dd($eval_goods_ids);
            if ($order_goods_ids != $eval_goods_ids) {
                abort(403, '非法操作：评价的商品和订单商品不匹配');
            }

            foreach (Request::input('eval') as $eval) {
                $validator = Validator::make($eval, [
                    'goods_id'          => 'required|integer',
                    'content'           => 'required|max:500',
                    'content_image'     => 'array',
                    'content_image.*'   => 'url',
                    'score'             => 'required|integer|in:1,2,3,4,5',
                ]);

                $validator->after(function ($validator) {
                    if (Request::has('content_image') && count(Request::input('content_image')) > 9) {
                        $validator->errors()->add('content_image', '最多上传5张评价图片');
                    }
                });

                if ($validator->fails()) {
                    abort(400, $validator->errors()->first());
                }

                // 获取spu_id
                $goods = MallModels\Goods::find($eval['goods_id']);
                if ($goods) {
                    $spu_id = $goods->spu->id;
                } else {
                    abort(400, 'goods_id不存在');
                }

                // 业务逻辑
                $evaluation = new Models\Evaluation();
                $evaluation->user_id    = $user_id;
                $evaluation->spu_id     = $spu_id;
                $evaluation->sku_id     = $eval['goods_id'];
                $evaluation->content    = $eval['content'];
                $evaluation->score      = $eval['score'];

                $evaluation->save();

                if (!empty($eval['content_image'])) {
                    // 移动七牛图片，然后把input的数据剔除域名
                    $idx = 1;
                    $content_image = [];
                    foreach ($eval['content_image'] as $image_url) {
                        $source = get_qiniu_key($image_url);

                        $path_parts = pathinfo($source);
                        $target = sprintf('/uploads/mall/goods_evaluation/%d/%d.%s', $evaluation->id, $idx, $path_parts['extension']);

                        move_qiniu_uploads($source, $target);

                        $content_image[] = $target;
                        $idx++;
                    }

                    $evaluation->content_image = json_encode($content_image, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
                    $evaluation->save();
                }

                // 改订单的评价状态
                $order->evaluation_status = 1;
                $order->save();
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }


    /**
     * 发布定制商品评价
     */
    public function customStore()
    {
        $res = parent::apiCreatedResponse();

        // dd(Request::all());
        // 通过token获取user_id
        $user_id = JWTAuth::user()->id;

        $validator = Validator::make(Request::all(), [
            //订单id
            'order_id'      => 'required|integer',
            //eval必须是 二维数组
            'eval'          => 'required|array',
            'eval.*'        => 'array',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            //根据order_id获取订单详情
            $order = Models\Order::find(Request::input('order_id'));
            // dd($order);
            //没有订单信息或者 用户的 id和查询到的用户id不同
            if (!$order || $order->user_id != $user_id) {
                abort(403, '非法操作');
            }

            //获取所有的 goodsid 组成一个数组
            // $order_goods_ids = $order->goods->pluck('goods_id')->all();
            // dd($order->goods->all());
            //获取eval中的 goods_id 字段   user_custom_log_id  用户需求详情id
            // $eval_goods_ids  = array_column(Request::input('eval'), 'goods_id');

            //对order_goods_ids 和 eval_goods_ids 排序
            // sort($order_goods_ids);
            // sort($eval_goods_ids);
            //如果 $order_goods_ids != $eval_goods_ids 则
            // dd($eval_goods_ids);
            // if ($order_goods_ids != $eval_goods_ids) {
            //     abort(403, '非法操作：评价的商品和订单商品不匹配');
            // }

            foreach (Request::input('eval') as $eval) {
                $validator = Validator::make($eval, [
                    'goods_id'          => 'required|integer',
                    'content'           => 'required|max:500',
                    'content_image'     => 'array',
                    'content_image.*'   => 'url',
                    'score'             => 'required|integer|in:1,2,3,4,5',
                ]);

                $validator->after(function ($validator) {
                    if (Request::has('content_image') && count(Request::input('content_image')) > 9) {
                        $validator->errors()->add('content_image', '最多上传5张评价图片');
                    }
                });

                if ($validator->fails()) {
                    abort(400, $validator->errors()->first());
                }

                // 获取spu_id
                /*$goods = MallModels\Goods::find($eval['goods_id']);
                if ($goods) {
                    $spu_id = $goods->spu->id;
                } else {
                    abort(400, 'goods_id不存在');
                }*/

                // 业务逻辑
                $evaluation = new Models\Evaluation();
                $evaluation->user_id    = $user_id;
                // $evaluation->spu_id     = $spu_id;
                $evaluation->goods_id     = $eval['goods_id'];
                $evaluation->content    = $eval['content'];
                $evaluation->score      = $eval['score'];

                //评价到此实际上就结束了
                $evaluation->save();

                if (!empty($eval['content_image'])) {
                    // 移动七牛图片，然后把input的数据剔除域名
                    $idx = 1;
                    $content_image = [];
                    foreach ($eval['content_image'] as $image_url) {
                        $source = get_qiniu_key($image_url);

                        $path_parts = pathinfo($source);
                        $target = sprintf('/uploads/mall/goods_evaluation/%d/%d.%s', $evaluation->id, $idx, $path_parts['extension']);

                        move_qiniu_uploads($source, $target);

                        $content_image[] = $target;
                        $idx++;
                    }

                    $evaluation->content_image = json_encode($content_image, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
                    $evaluation->save();
                }

                // 改订单的评价状态
                $order->evaluation_status = 1;
                $order->save();
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }


}
