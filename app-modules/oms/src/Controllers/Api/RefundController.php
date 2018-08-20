<?php

namespace Modules\Oms\Controllers\Api;

use Modules\Oms\Models;
use Request;
use Validator;
use JWTAuth;
use Illuminate\Validation\Rule;

class RefundController extends \BaseController
{
    public function index($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            $refund = Models\Refund::select('refund_status', 'new_order_id', 'refund_type', 'reason', 'refund_amount', 'created_at', 'finished_at', 'goods_id', 'order_id')
                    ->where('order_id', $id)
                    ->where('user_id', JWTAuth::user()->id)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->each(function ($item) {
                        $order_goods = $item->orderGoods()->where('goods_id', $item->goods_id)->first();
                        $item->goods_name = $order_goods->goods_name;
                        $item->goods_price = $order_goods->goods_price;
                        $item->goods_num = $order_goods->goods_num;
                        $item->goods_image = $order_goods->goods_image;
                        $item->goods_spec = $order_goods->goods_spec;
                        unset($item->order_id);
                    });

            $res['data'] = $refund;
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    # 申请售后
    public function store($id)
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'goods_id'      => 'required|integer',
            'is_receive'    => [
                'required',
                Rule::in([0, 1]),
            ],
            'refund_type'   => [
                'required',
                Rule::in([1, 2, 3]),
            ],
            'reason'        => 'required',
            'description'   => 'max:500',
            'image'         => 'array',
            'image.*'       => 'url',
        ]);

        // 当申请类型为1（仅退款），退款金额为必填
        $validator->sometimes('refund_amount', 'required|numeric', function ($input) {
            return $input->refund_type == 1;
        });

        // 当收货状态为0（未签收），只允许申请类型为1（仅退款）
        $validator->sometimes('refund_type', 'min:1|max:1', function ($input) {
            return $input->is_receive == 0;
        });

        // 当有图片时，最多5张
        $validator->after(function ($validator) {
            if (Request::has('image') && count(Request::input('image')) > 5) {
                $validator->errors()->add('image', '最多上传5张图片');
            }
        });

        try {
            // 如果Validator不通过，抛异常
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }
            // 验证订单存在，且是该用户的
            $order = Models\Order::where('id', $id)->where('user_id', JWTAuth::user()->id)->first();
            if (!$order) {
                abort(404, '用户不存在该订单！');
            }
            // 验证订单状态 order_status:[20,30,40]
            if (!in_array($order->order_status, [20, 30, 40])) {
                abort(403, '该订单不能申请售后！');
            }
            // 验证订单售后记录 refund_record != 2
            if ($order->refund_record == 2) {
                abort(403, '该订单不能申请售后！');
            }
            // 验证订单商品存在
            $order_goods = Models\OrderGoods::where('order_id', $id)->where('goods_id', Request::input('goods_id'))->first();
            if (!$order_goods) {
                abort(404, '订单不存在该商品！');
            }
            // 验证oms_refund表，不存在相同未完成记录
            $old_refund = Models\Refund::where('order_id', $id)->where('goods_id', Request::input('goods_id'))->where('refund_status', 1)->first();
            if ($old_refund) {
                abort(403, '已经有一个处理中的服务，不能重复提交！');
            }

            // 创建售后实例
            $refund = new Models\Refund;
            $refund->user_id = JWTAuth::user()->id;
            $refund->order_id = $id;
            $refund->goods_id = Request::input('goods_id');
            $refund->refund_type = Request::input('refund_type');
            $refund->is_receive = Request::input('is_receive');
            $refund->reason = Request::input('reason');

            if (Request::has('refund_amount') && Request::input('refund_type') == 1) {
                $refund->refund_amount = Request::input('refund_amount');
            }
            if (Request::has('description')) {
                $refund->description = Request::input('description');
            }
            $refund->save();

            if (Request::has('image') && count(Request::input('image')) > 0) {
                // 移动七牛图片，然后把input的数据剔除域名
                $idx = 1;
                $image = [];
                foreach (Request::input('image') as $image_url) {
                    $source = get_qiniu_key($image_url);

                    $path_parts = pathinfo($source);
                    $target = sprintf('/uploads/oms/refund/%d/%d.%s', $refund->id, $idx, $path_parts['extension']);

                    move_qiniu_uploads($source, $target);

                    $image[] = $target;
                    $idx++;
                }

                $refund->image = json_encode($image, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
                $refund->save();
            }

            //修改订单状态
            $order = Models\Order::find($id);
            $order->refund_status = 1;
            $order->refund_record = 1;
            $order->save();
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
