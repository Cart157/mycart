<?php

namespace Modules\Base\Controllers\Common;

use Modules\Base\Libraries\Captcha\Sms\SmsCaptcha;
use Modules\Base\Libraries\Captcha\Sms\SmsAppCaptcha;
use Modules\Base\Libraries\Captcha\Image\ImageCaptcha;
use Request;
use Validator;
use Config;
use Session;

class CaptchaController extends \BaseController
{
    public function apiGet()
    {
        // 读取配置文件
        $config = Config::get('captcha.lyric');

        // 随机取一首歌
        $song = array_rand($config);
        $lyricList = $config[$song];

        // 随机取一句词
        $rand_key = array_rand($lyricList);
        $lyric = $lyricList[$rand_key];

        // 随机生成问题和答案
        $rand_len = 2;//rand(2, 3);■■
        $rand_pos = rand(0, mb_strlen($lyric) - 1 - $rand_len);
        $answer   = mb_substr($lyric, $rand_pos, $rand_len);
        $question = str_replace($answer, ' _ _ ', $lyric);

        // 把答案存到Session的闪存里
        Session::flash('captcha', $answer);

        $result = [
            'result'   => true,
            'question' => "<strong>《{$song}》</strong><br>".$question,
        ];

        return $result;
    }

//手机验证码
    //@URL post:api/common/sms
    public function sms()
    {
        $captcha = new SmsCaptcha();

        try {
            $target = Request::input('mobile');
            $captcha->send($target);
//            $captcha->send('13820693412');
//            $captcha->send('13207606059');

            $result = [
                'result'   => 200,
                'message'  => '验证码已发送，请注意查收（三分钟内有效）',
            ];
        } catch (\Exception $e) {
            $result = [
                'result'   => 500,
                'message'  => '验证码已失败，请重试',
            ];
        }

        return $result;
    }

    public function smsApp()
    {
        $res = parent::apiFetchedResponse();

        $captcha = new SmsAppCaptcha();

        // todo:验证码
        $validator = Validator::make(Request::all(), [
            'mobile'    => 'required|digits:11',
        ]);

        try {
            $target = Request::input('mobile');
            $captcha->send($target);

            $res['message'] = '验证码已发送，请注意查收（三分钟内有效）';
        } catch (\Exception $e) {
            parent::apiException($e, $res);
            $res = '验证码已失败，请重试';
        }

        return $res;
    }

    public function image()
    {
        $captcha = new ImageCaptcha();

        try {
            $captcha->send();

            $result = [
                'result'   => 200,
                'message'  => '验证码已发送，请注意查收（三分钟内有效）',
            ];
        } catch (\Exception $e) {
            $result = [
                'result'   => 500,
                'message'  => '验证码已失败，请重试',
            ];
        }

        return $result;


        // 读取配置文件
        $config = Config::get('captcha.lyric');

        // 随机取一首歌
        $song = array_rand($config);
        $lyricList = $config[$song];

        // 随机取一句词
        $rand_key = array_rand($lyricList);
        $lyric = $lyricList[$rand_key];

        // 随机生成问题和答案
        $rand_len = 2;//rand(2, 3);■■
        $rand_pos = rand(0, mb_strlen($lyric) - 1 - $rand_len);
        $answer   = mb_substr($lyric, $rand_pos, $rand_len);
        $question = str_replace($answer, ' _ _ ', $lyric);

        // 把答案存到Session的闪存里
        Session::flash('captcha', $answer);

        $result = [
            'result'   => true,
            'question' => "<strong>《{$song}》</strong><br>".$question,
        ];

        return $result;
    }

    public function lyric()
    {
        // 读取配置文件
        $config = Config::get('captcha.lyric');

        // 随机取一首歌
        $song = array_rand($config);
        $lyricList = $config[$song];

        // 随机取一句词
        $rand_key = array_rand($lyricList);
        $lyric = $lyricList[$rand_key];

        // 随机生成问题和答案
        $rand_len = 2;//rand(2, 3);■■
        $rand_pos = rand(0, mb_strlen($lyric) - 1 - $rand_len);
        $answer   = mb_substr($lyric, $rand_pos, $rand_len);
        $question = str_replace($answer, ' _ _ ', $lyric);

        // 把答案存到Session的闪存里
        Session::flash('captcha', $answer);

        $result = [
            'result'   => true,
            'question' => "<strong>《{$song}》</strong><br>".$question,
        ];

        return $result;
    }
}
