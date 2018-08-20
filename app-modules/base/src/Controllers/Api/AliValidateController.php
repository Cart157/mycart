<?php

namespace Modules\Base\Controllers\Api;
use Request;
use Validator;
class AliValidateController extends \BaseController
{
    /**
     * 图片同步
     * @return mixed
     */
    public function imageSync()
    {
        $res = parent::apiFetchedResponse();
        $validator = Validator::make(Request::all(), [
            'image' => 'required',

        ]);
        try {

            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $res['data'] = parent::validateImageSync(Request::input('image'));
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 图片异步
     * @return mixed
     */
    public function imageAsync()
    {
        $res = parent::apiFetchedResponse();
        $validator = Validator::make(Request::all(), [
            'image' => 'required',

        ]);
        try {

            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $res['data'] = parent::validateImageAsync(Request::input('image'));
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 视频异步检验
     * @return mixed
     */
    public function videoAsync()
    {
        $res = parent::apiFetchedResponse();
        $validator = Validator::make(Request::all(), [
            'video' => 'required',

        ]);
        try {

            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $res['data'] = parent::validateVideoAsync(Request::input('video'));
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}