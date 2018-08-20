<?php

namespace Modules\Base\Controllers\Api\CloudStorage;

use Qiniu\Storage\UploadManager;
use Qiniu\Auth;
use Validator;
class QiniuController extends \BaseController
{
    const ALLOW_PREFIX = 'uploads/_tmp/';

    /**
     * 七牛上传token
     * @return mixed
     */
    public function token()
    {
        $res = parent::apiFetchedResponse();

        $access_key    = config('qiniu.access_key');
        $secret_key    = config('qiniu.secret_key');
        $bucket        = config('qiniu.bucket');

        try {
            // $up_manager = new UploadManager();
            $auth = new Auth($access_key, $secret_key);

            // 限定前缀，只能传到 'uploads/_tmp/' 里去
            $token = $auth->uploadToken($bucket, self::ALLOW_PREFIX, 3600, ['isPrefixalScope' => 1]);

            // 返回数据
            $res['data']['upload_token'] = $token;

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 七牛图片搜索
     * @param $body
     * @return mixed
     */
    public function imageResemble()
    {
        $res = parent::apiFetchedResponse();
        $validator = Validator::make(request()->all(), [
            'method' => 'required',
            'url' => 'required'
        ]);
        $access_key    = config('qiniu.access_key');
        $secret_key    = config('qiniu.secret_key');
        //$bucket        = config('qiniu.bucket');

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }
            $auth = new Auth($access_key, $secret_key);
            //$url = "http://argus.atlab.ai/v1/image/group/1/add";
            $url = request('url');

            $body = null;
            $contentType = null;
            $method = strtoupper(request('method'));
            if (request()->has('body'))
            {
                $contentType = "application/json";
                $body = request('body');
            }
            $jqToken = $auth->authorizationV2($url, $method, $body, $contentType);

            $res['data'] = qiniu_resemble_image($jqToken,$method,$url,$body);
            // 返回数据
            //$res['data']['image_token'] = $jqToken;

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
