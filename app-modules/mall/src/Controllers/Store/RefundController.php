<?php

namespace Modules\Mall\Controllers\Store;

use Modules\Mall\Models;
use Modules\Oms\Models as OmsModels;
use Validator;
use Illuminate\Validation\Rule;
use Request;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;

class RefundController extends \BaseController
{
    public function index()
    {
        $q = OmsModels\Refund::whereNull('deleted_at');

        // 申请类型
        if (Request::has('refund_type')) {
            $q->where('refund_type', Request::input('refund_type'));
        }

        // 处理状态
        if (Request::has('refund_status')) {
            // 自定refund_status 39：进行中的订单
            if (Request::input('refund_status') == 39) {
                $q->where('refund_status', '>', 0)->where('refund_status', '<', '40');
            } else {
                $q->where('refund_status', Request::input('refund_status'));
            }
        }

        // 订单状态
        if (Request::has('order_status')) {
            // 自定order_status 31：order_status == 30、40
            if (Request::input('order_status') == 31) {
                $q->whereHas('order', function ($query) {
                    $query->where('order_status', 30)->orWhere('order_status', 40);
                });
            } else {
                $q->whereHas('order', function ($query) {
                    $query->where('order_status', Request::input('order_status'));
                });
            }
        }

        // 售中、售后类型
        if (Request::has('sale_type')) {
            if (Request::input('sale_type') == 'sale') {
                $q->whereHas('order', function ($query) {
                    $query->where('order_status', '<>', 40);
                });
            } elseif (Request::input('sale_type') == 'after-sale') {
                $q->whereHas('order', function ($query) {
                    $query->where('order_status', 40);
                });
            }
        }

        // 买家昵称
        if (Request::has('user_name')) {
            $q->whereHas('user', function ($query) {
                $query->where('name', 'like', '%'.Request::input('user_name').'%');
            });
        }

        // 订单编号
        if (Request::has('order_no')) {
            $q->whereHas('order', function ($query) {
                $query->where('order_no', 'like', '%'.Request::input('order_no').'%');
            });
        }

        // 退款ID
        if (Request::has('id')) {
            $q->where('id', Request::input('id'));
        }

        // 退款时间
        if (Request::has('finished_at')) {
            if (Request::input('finished_at') == 'recent') {
                $latest_time = date('Y-m-d h:i:s', strtotime(time() - (24 * 3600 * 3)));
            } elseif (Request::input('finished_at') == 'month') {
                $latest_time = date('Y-m-d h:i:s', strtotime(time() - (24 * 3600 * 30)));
            } elseif (Request::input('finished_at') == 'half_year') {
                $latest_time = date('Y-m-d h:i:s', strtotime(time() - (24 * 3600 * 180)));
            }
            $q->where('finished_at', '<=', $latest_time);
        }

        // 运单编号
        if (Request::has('waybill_no')) {
            $q->whereHas('detail', function ($query) {
                $query->where('option_type', 2)->where('refund_waybill_no', 'like', '%'.Request::input('waybill_no').'%');
            });
        }

        // 申请时间
        if (Request::has('created_from')) {
            $q->where('created_at', '>=', Request::input('created_from'));
        }
        if (Request::has('created_to')) {
            $q->where('created_at', '<=', Request::input('created_to'));
        }

        // 修改时间
        if (Request::has('updated_from')) {
            $q->where('updated_at', '>=', Request::input('updated_from'));
        }
        if (Request::has('updated_to')) {
            $q->where('updated_at', '<=', Request::input('updated_to'));
        }

        // 退款金额
        if (Request::has('refund_amount_from')) {
            $q->where('refund_amount', '>=', Request::input('refund_amount_from'));
        }
        if (Request::has('refund_amount_to')) {
            $q->where('refund_amount', '<=', Request::input('refund_amount_to'));
        }

        $ret = $q->orderBy('id', 'desc')->paginate(10);

        $result['data'] = $ret;

        $not_finished = OmsModels\Refund::where('refund_status', '<>', 40)->where('refund_status', '<>', 0)->get();
        $count['not_finished'] = count($not_finished);
        $wait_buyer_delivery = OmsModels\Refund::where('refund_status', 20)->get();
        $count['wait_buyer_delivery'] = count($wait_buyer_delivery);
        $wait_deal = OmsModels\Refund::where('refund_status', 10)->get();
        $count['wait_deal'] = count($wait_deal);
        $refused = OmsModels\Refund::where('refund_status', 0)->get();
        $count['refused'] = count($refused);
        $wait_seller_delivery =  OmsModels\Refund::whereNotNull('new_order_id')->whereHas('newOrder', function($query) {
            $query->where('order_status', 20);
        })->get();
        $count['wait_seller_delivery'] = count($wait_seller_delivery);

        $result['count'] = $count;

        return view('mall::store.refund.index', $result);
    }

    public function show($id)
    {
        try {
            $refund = OmsModels\Refund::findOrFail($id);

            $result['data'] = $refund;

            if ($refund->refund_type == 1 && $refund->order->order_status == 20) {
                return view('mall::store.refund.refundNotShipped', $result);
            } elseif ($refund->refund_type == 1 && $refund->order->order_status != 20) {
                return view('mall::store.refund.refundShipped', $result);
            } elseif ($refund->refund_type == 2) {
                return view('mall::store.refund.returnGoods', $result);
            } elseif ($refund->refund_type == 3) {
                return view('mall::store.refund.exchange', $result);
            }
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function updateRemark($id)
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'remark'    => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->fails());
            }

            $refund = OmsModels\Refund::findOrFail($id);

            $refund->seller_remark = Request::input('remark');
            $refund->save();
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function reject($id)
    {
        //dd(Request::all());

        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'option_type'           => [
                'required',
                Rule::in([1, 3]),
            ],
            'reject_reason'         => 'required|max:100',
            'reject_image'          => 'array',
            'seller_description'    => 'required|max:500',
        ]);

        try {
            // 验证字段是否符合要求
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 验证是否存在该退款记录
            $refund = OmsModels\Refund::findOrFail($id);

            // 验证是否可以拒绝
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

            $reject_image = [];
            if (Request::has('reject_image')) {
                //设置存储图片目标路径
                $filesystem = new Filesystem();
                $target_dir = public_path().sprintf('/uploads/mall/refund/%d', $id);

                //存储图片&写数据库
                foreach (Request::input('reject_image') as $key => $path) {
                    //存储图片目标路径不存在则新建文件夹
                    if (!$filesystem->exists($target_dir)) {
                        $filesystem->makeDirectory($target_dir, 0755 ,true);
                    }

                    // 移动图片
                    $target_filename = basename($path);
                    $target_path = sprintf('/uploads/mall/refund/%d/%s', $id, $target_filename);
                    $filesystem->move(public_path().$path, public_path().$target_path);

                    $reject_image[] = $target_path;
                }
            }

            // 验证通过，执行拒绝请求
            $detail = OmsModels\RefundDetail::create([
                'refund_id'         => $id,
                'refund_type'       => $refund->refund_type,
                'option_type'       => Request::input('option_type'),
                'option_value'      => 0,
                'seller_description'=> Request::input('seller_description'),
                'reject_reason'     => Request::input('reject_reason'),
                'reject_image'      => json_encode($reject_image, JSON_UNESCAPED_SLASHES),
            ]);

            $refund->refund_status = 0;
            $refund->finished_at = new Carbon();
            $refund->save();

            if ($refund->order->order_status == 20) {
                return redirect('/store/logistics/delivery/'.$refund->order_id);
            } else {
                return redirect('/store/refund/'.$id.'/edit');
            }
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function agreeApply($id)
    {
        //dd(Request::all());

        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'consigner_refund_id'   => 'required|integer',
            'seller_description'    => 'required|max:500',
        ]);

        try {
            // 验证字段是否符合要求
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 验证是否存在该退款记录
            $refund = OmsModels\Refund::findOrFail($id);

            // 验证是否可以同意
            if ($refund->refund_status != 10 || $refund->detail->toArray() != []) {
                abort(403, '不能同意申请！');
            }

            // 验证通过，执行同意申请
            $detail = OmsModels\RefundDetail::create([
                'refund_id'             => $id,
                'refund_type'           => $refund->refund_type,
                'option_type'           => 1,
                'option_value'          => 1,
                'seller_description'    => Request::input('seller_description'),
                'consigner_refund_id'   => Request::input('consigner_refund_id'),
            ]);

            $refund->refund_status = 20;
            $refund->save();

            return redirect('/store/refund/'.$id.'/edit');
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
