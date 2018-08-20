<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Libraries\Captcha\Sms\SmsCaptcha;
use Request;
use Validator;
use DB;
//use Config;
//use Session;

class CaptchaController extends \BaseController
{
    public function sms()
    {
        $res = parent::apiFetchedResponse();

        $captcha = new SmsCaptcha();

        // todo:验证码 |digits:11
        $validator = Validator::make(Request::all(), [
            'tpl'       => 'required',//'in:regist,reset,bind,auth,login,unusual,delete,pay_code_alter,ali_bind,ali_reset',
            'mobile'    => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $tpl = Request::input('tpl');
            $target = Request::input('mobile');

            $exist_captcha = cache('sms_captcha'.$target);
            if (!$exist_captcha) {
                $captcha->send($tpl, $target);
                $res['message'] = '验证码已发送，请注意查收（'.env('MOBILE_CODE_EXPIRE',3).'分钟内有效）';
            } else {
                $res['message'] = '您之前收到的验证码 '. $exist_captcha .' 仍然在有效期内（'.env('MOBILE_CODE_EXPIRE',3).'分钟内有效）';
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function code()
    {
        $res = parent::apiFetchedResponse();
        try {

            $res['data'] = DB::table('base_sms_code')
                ->when(request()->has('sn'), function ($query)  {
                return $query->where('sn', 'like','%'.request('sn').'%');})
                ->when(request()->has('en'), function ($query)  {
                    return $query->where('en', 'like','%'.request('en').'%');})
                ->orderBy('sort_order','desc')->get();
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
//    public function apiGet()
//    {
//        // 读取配置文件
//        $config = Config::get('captcha.lyric');
//
//        // 随机取一首歌
//        $song = array_rand($config);
//        $lyricList = $config[$song];
//
//        // 随机取一句词
//        $rand_key = array_rand($lyricList);
//        $lyric = $lyricList[$rand_key];
//
//        // 随机生成问题和答案
//        $rand_len = 2;//rand(2, 3);■■
//        $rand_pos = rand(0, mb_strlen($lyric) - 1 - $rand_len);
//        $answer   = mb_substr($lyric, $rand_pos, $rand_len);
//        $question = str_replace($answer, ' _ _ ', $lyric);
//
//        // 把答案存到Session的闪存里
//        Session::flash('captcha', $answer);
//
//        $result = [
//            'result'   => true,
//            'question' => "<strong>《{$song}》</strong><br>".$question,
//        ];
//
//        return $result;
//    }
}
