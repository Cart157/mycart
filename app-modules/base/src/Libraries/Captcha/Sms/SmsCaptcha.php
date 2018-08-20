<?php

namespace Modules\Base\Libraries\Captcha\Sms;

use Modules\Base\Libraries\Captcha\CaptchaInterface;
use Overtrue\EasySms\EasySms;
use Log;

/*
阿里云手机验证码
*/
class SmsCaptcha implements CaptchaInterface
{
    // 一个验证码可尝试3次
    const RETRY_MAX = 3;

    protected $target;
    //模版
    protected $tplArr = [
        //国内
        'login'     => 'SMS_94285117',//登录确认验证码
        'unusual'   => 'SMS_94285116',//登录异常验证码
        'auth'      => 'SMS_94285119',//身份验证验证码
        'regist'    => 'SMS_94285115',//用户注册验证码
        'reset'     => 'SMS_94285114',//修改密码验证码
        'bind'      => 'SMS_94285113',//信息变更验证码
        'delete'    => 'SMS_127154034',//删除重要内容时验证码
        'pay_code_alter'   => 'SMS_133155174',//修改支付密码
        'ali_bind' => 'SMS_133170129',//支付宝绑定
        'ali_reset' => 'SMS_133170170',//支付密码设置
        //国际
        'im_login'     => 'SMS_135030745',//登录确认验证码
        'im_unusual'   => 'SMS_135040764',//登录异常验证码
        'im_auth'      => 'SMS_135030743',//身份验证验证码
        'im_regist'    => 'SMS_135040770',//用户注册验证码
        'im_reset'     => 'SMS_135026236',//修改密码验证码
        'im_bind'      => 'SMS_135036062',//信息变更验证码
        'im_delete'    => 'SMS_135040762',//删除重要内容时验证码
        'im_pay_code_alter'   => 'SMS_135035730',//修改支付密码
        'im_ali_bind' => 'SMS_135025913',//支付宝绑定
        'im_ali_reset' => 'SMS_135030742',//支付密码设置
    ];

    //生成验证码
    public function generate()
    {
        $captcha_rand = mt_rand(0, str_repeat(9,env('MOBILE_CODE_LENGTH',6)));
        $captcha_code = str_pad($captcha_rand,  env('MOBILE_CODE_LENGTH',6), '0');

        // 3分钟失效
        cache(['sms_captcha'.$this->target => $captcha_code], env('MOBILE_CODE_EXPIRE',3));
        cache(['sms_captcha_try'.$this->target => 0], env('MOBILE_CODE_EXPIRE',3));

        return $captcha_code;
    }


    //发送验证码
    public function send($tpl, $target, $code = null)
    {
        $this->target = $target;
        //生成验证码
        $captcha_code = $code ?: $this->generate();

        $config = [
            // HTTP 请求的超时时间（秒）
            'timeout' => 5.0,

            // 默认发送配置
            'default' => [
                // 网关调用策略，默认：顺序调用
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

                // 默认可用的发送网关
                'gateways' => [
                    'aliyun',
                ],
            ],
            // 可用的网关配置
            'gateways' => [
                'errorlog' => [
                    'file' => '/tmp/easy-sms.log',
                ],
                'aliyun' => [
                    'access_key_id'     => config('aliyun.access_key_id'),
                    'access_key_secret' => config('aliyun.access_key_secret'),
                    'sign_name' => 'BAN',
                ],
            ],
        ];

        $easySms = new EasySms($config);

        $easySms->send($target, [
            'template' => $this->tplArr[$tpl],
            'data' => [
                'code' => $captcha_code
            ],
        ]);
    }


    //验证验证码
    public function validate($target, $code)
    {
        $this->target = $target;
        $sms_captcha        = cache('sms_captcha'.$target);
        $sms_captcha_try    = cache('sms_captcha_try'.$target);

        if (!$sms_captcha || $sms_captcha_try > self::RETRY_MAX) {
            $this->destroy();
            throw new \Exception('验证码已失效');
        }

        if ($code == $sms_captcha) {
            $this->destroy();
            return true;
        } elseif ($sms_captcha_try < self::RETRY_MAX) {
            $retry = $sms_captcha_try + 1;
            cache()->put('sms_captcha_try'.$target, $retry, 3);

            throw new \Exception('验证码错误，请重新输入');
        } else {
            $this->destroy();
            throw new \Exception('验证码错误，请重新获取');
        }
    }

    public function destroy()
    {
        cache()->forget('sms_captcha'.$this->target);
        cache()->forget('sms_captcha_try'.$this->target);
    }
}
