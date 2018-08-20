<?php

namespace Modules\Base\Controllers\Api;

use Request;
use Validator;

class LocationController extends \BaseController
{
    public function index()
    {
        $res = parent::apiFetchedResponse();

        $validator = Validator::make(Request::all(), [
            'code'      => 'digits:6',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 业务逻辑
            if (Request::has('code')) {
                $location_tmp = location(Request::input('code'), 'list') ?: [];
            } else {
                $location_tmp = location() ?: [];
            }

            $location = [];
            foreach ($location_tmp as $key => $value) {
                $location[] = [
                    'area_code' => $key,
                    'area_name' => $value,
                ];
            }

            $res['data'] = $location;
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function show($code)
    {
        $res = parent::apiFetchedResponse();

        $validator = Validator::make(Request::all(), [
            'code'      => 'digits:6',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $res['data'] = [
                'area_code' => $code,
                'area_name' => Request::input('detail') ? location($code, 'detail') : location($code),
            ];
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
