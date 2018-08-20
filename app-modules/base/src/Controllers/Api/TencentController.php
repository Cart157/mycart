<?php

namespace Modules\Base\Controllers\Api;
use Request;
use Validator;
class TencentController extends \BaseController
{
    /**
     * 签名
     * @return mixed
     */
    public function signature()
    {
        $res = parent::apiFetchedResponse();

        try {

            // 确定 App 的云 API 密钥
            $secret_id = config('tencent.SecretId');
            $secret_key = config('tencent.SecretKey');

            // 确定签名的当前时间和失效时间
            $current = time();
            $expired = $current + config('tencent.time');  // 签名有效期：1天

            // 向参数列表填入参数
            $arg_list = array(
                "secretId" => $secret_id,
                "currentTimeStamp" => $current,
                "expireTime" => $expired,
                "random" => rand());

            // 计算签名
            $orignal = http_build_query($arg_list);
            $res['data'] = base64_encode(hash_hmac('SHA1', $orignal, $secret_key, true).$orignal);


        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }


}