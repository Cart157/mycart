<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Models;
use Request;
use Validator;
use JWTAuth;

class ReportController extends \BaseController
{
    public function store($id)
    {
        if ($id != JWTAuth::user()->id) {
            abort(500, '非法操作，不能更新');
        }

        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'id'        => 'required|numeric',
            'type'      => 'required|in:user,moment,comment',
            'reason'    => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // XXX:此处不验证item_id是否存在

            // 业务逻辑
            $new_item = Models\UserReport::create([
                'user_id'   => $id,
                'item_type' => Request::input('type'),
                'item_id'   => Request::input('id'),
                'reason'    => Request::input('reason'),
            ]);

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
