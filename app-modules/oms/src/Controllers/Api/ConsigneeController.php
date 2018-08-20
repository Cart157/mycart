<?php

namespace Modules\Oms\Controllers\Api;

use Modules\Oms\Models;
use Request;
use Validator;
use JWTAuth;

class ConsigneeController extends \BaseController
{
    /**
     * 列出用户的收获地址
     */
    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            // 通过token获取user_id
            $user_id = JWTAuth::user()->id;

            $consignees = Models\Consignee::where('user_id', $user_id)
                                          ->orderBy('is_default', 'desc')
                                          ->orderBy('updated_at', 'desc')
                                          ->get();

            // 整理数据
            $consignees->each(function($item) {
                $item->addHidden(['created_at', 'updated_at', 'deleted_at']);
            });

            $res['data'] = $consignees;

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 添加收货地址
     */
    public function store()
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'consignee_name'    => 'required',
            'province_code'     => 'required|digits:6',
            'city_code'         => 'required|digits:6',
            'area_code'         => 'required|digits:6',
            'address'           => 'required',
            'mb_phone'          => 'required|digits:11',
            'is_default'        => 'required|in:0,1',
            // 'tel_phone'         => 'required',
            // 'zip_code'          => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            if (substr(Request::input('area_code'), 0, 2) . '0000' != Request::input('province_code')
              || substr(Request::input('area_code'), 0, 4) . '00' != Request::input('city_code')) {
                abort(500, '非法操作');
            }

            // 通过token获取user_id
            $user_id = JWTAuth::user()->id;

            // 第一个收货地址设为默认
            $current_cnt = Models\Consignee::where('user_id', $user_id)->count();
            if ($current_cnt == 0) {
                Request::merge(['is_default' => 1]);
            }

            $consignee = new Models\Consignee();
            $consignee->user_id         = $user_id;
            $consignee->consignee_name  = Request::input('consignee_name');
            $consignee->province_code   = Request::input('province_code');
            $consignee->city_code       = Request::input('city_code');
            $consignee->area_code       = Request::input('area_code');
            $consignee->area_info       = location(Request::input('area_code'), 'detail');
            $consignee->address         = Request::input('address');
            $consignee->mb_phone        = Request::input('mb_phone');
            $consignee->is_default      = Request::input('is_default');
            $consignee->save();

            if (Request::input('is_default')) {
                // 更新其他为非默认
                Models\Consignee::where('user_id', $user_id)
                                ->where('id', '<>', $consignee->id)
                                ->update(['is_default' => 0]);
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 更新收货地址
     */
    public function update($id)
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'consignee_name'    => 'required',
            'province_code'     => 'required|digits:6',
            'city_code'         => 'required|digits:6',
            'area_code'         => 'required|digits:6',
            'address'           => 'required',
            'mb_phone'          => 'required|digits:11',
            'is_default'        => 'required|in:0,1',
            // 'tel_phone'         => 'required',
            // 'zip_code'          => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            if (substr(Request::input('area_code'), 0, 2) . '0000' != Request::input('province_code')
              || substr(Request::input('area_code'), 0, 4) . '00' != Request::input('city_code')) {
                abort(500, '非法操作');
            }

            // 通过token获取user_id
            $user_id = JWTAuth::user()->id;

            $consignee = Models\Consignee::findOrFail($id);

            if ($consignee->user_id != JWTAuth::user()->id) {
                abort(500, '非法操作');
            }

            $consignee->consignee_name  = Request::input('consignee_name');
            $consignee->province_code   = Request::input('province_code');
            $consignee->city_code       = Request::input('city_code');
            $consignee->area_code       = Request::input('area_code');
            $consignee->area_info       = location(Request::input('area_code'), 'detail');
            $consignee->address         = Request::input('address');
            $consignee->mb_phone        = Request::input('mb_phone');
            $consignee->is_default      = Request::input('is_default');

            if (Request::has('tel_phone')) {
                $consignee->tel_phone   = Request::input('tel_phone');
            } else {
                $consignee->tel_phone   = null;
            }

            if (Request::has('zip_code')) {
                $consignee->zip_code    = Request::input('zip_code');
            } else {
                $consignee->zip_code    = null;
            }

            $consignee->save();

            if (Request::input('is_default')) {
                // 更新其他为非默认
                Models\Consignee::where('user_id', $user_id)
                                ->where('id', '<>', $consignee->id)
                                ->update(['is_default' => 0]);
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 删除收货地址
     */
    public function destroy($id)
    {
        $res = parent::apiDeletedResponse();

        try {
            // 业务逻辑
            $consignee = Models\Consignee::findOrFail($id);

            if ($consignee->user_id != JWTAuth::user()->id) {
                abort(500, '非法操作');
            }

            // 如果删除的是默认地址，再设置一个默认
            if ($consignee->is_default) {
                $first = Models\Consignee::where('user_id', $consignee->user_id)
                                         ->where('id', '<>', $consignee->id)
                                         ->orderBy('updated_at', 'desc')
                                         ->first();

                if ($first) {
                    $first->is_default = 1;
                    $first->save();
                }
            }

            // 硬删除
            $consignee->forceDelete();

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
