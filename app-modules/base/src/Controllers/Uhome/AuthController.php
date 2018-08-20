<?php

namespace Modules\Base\Controllers\Uhome;

use Modules\Base\Models;
use Exception;
use App;
use Auth;
use Request;
use Validator;
// use JWTAuth;

class AuthController extends \BaseController
{
    public function __construct()
    {
        $this->middleware('uhome.logined', ['except' => 'logout']);
    }

    /**
     * 生成二维码登录
     * @param $qruu_id  生成二维码对应id
     */
    public function login()
    {
        // cache('sms_captcha'.$target);
        // cache(['sms_captcha'.$this->target => $captcha_code], env('MOBILE_CODE_EXPIRE',3));

        do {
            $md5_uniqid = md5(uniqid(microtime(true), true));
        } while (cache('qrlogin_'. $md5_uniqid));

        cache(['qrlogin_'. $md5_uniqid => 0], 1);
        $query_data = [
            'uuid'  => $md5_uniqid,
            'from'  => 'uhome',
        ];
        $qr_text = urlencode('https://www.tosneaker.com/qrlogin?'. http_build_query($query_data));
        // $qr_text = 'qrlogin_'. $md5_uniqid;
        $qr_code = '<img src ="http://qr.topscan.com/api.php?el=m&w=150&m=5&text=' .$qr_text. '">';

        return view('base::uhome.auth.login', [
            'qr_code'       => $qr_code,
            'md5_uniqid'    => $md5_uniqid,
        ]);
    }

    // /**
    //  * 1.轮训qruuid是否绑定
    //  * 2.绑定成功进行跳转
    //  */
    // public function checkQruuId()
    // {
    //     $qruu_id = Request::input('qruu_id');
    //     $qruu = Models\Qruu::where('qruu_id',$qruu_id)->first();
    //     if ($qruu) {

    //         $user = Models\User::find($qruu->user_id);

    //         if (!$user->roleIs('author')) {
    //             abort(403, '您的角色无权登录这个系统');
    //         }

    //         $res['data'] = [
    //             'user_info'     => $user,
    //             'token'         => \JWTAuth::fromUser($user),
    //         ];
    //     } else {
    //         $res['data'] = false;
    //     }
        
    //     return $res;
    // }

    /**
     * 确定登录:获取用户信息写入数据库
     *    
     */
    public function doLogin() 
    {
        $user_id = JWTAuth::user()->user_id;
        $qruu_id = Request::input('qruu_id');
        $qruu    = Models\Qruu::where('qruu_id',$qruu_id)->update(['user_id' => $user_id]);
        return (boolean) $qruu;
    }

    public function postLogin()
    {

        $res = parent::apiCreatedResponse();
        //return Request::all();
        // todo:验证码
        $validator = Validator::make(Request::all(), [
            'mobile'    => 'required|digits:11',
            'password'  => 'required',
        ]);

        try {

            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }
            $user_profile = Models\UserProfile::where('mobile', Request::input('mobile'))->first();
            $user         = $user_profile->user;
            if (!$user) {
                abort(401, '账号或密码错误');
            }
            if (!$user->roleIs('author')) {
                abort(403, '您的角色无权登录这个系统');
            }
            // 整理返回数据
            $user->addHidden(['created_at', 'updated_at', 'deleted_at']);

            $res['data'] = [
                'user_info'     => $user,
                'token'         => \JWTAuth::fromUser($user),
                'token_exp_in'  => config('jwt.ttl') * 60,
            ];
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 二维码登录轮询api
     */
    public function qrLoginQuery()
    {
        $res = parent::apiFetchedResponse();

        $uuid = Request::input('uuid');
        $from = Request::input('from');

        // 记录的是 user_id和是否确认
        $qr_value = cache('qrlogin_'. $uuid);

        if (is_null($qr_value)) {
            // 说明过期了，提示前台网页过期重刷
            $res['status'] = '403';
            $res['message'] = '二维码已过期<br>请重新刷新页面';
            return $res;
        }

        if ($qr_value == 0) {
            // 说明用户还没扫，前台网页会继续轮询
            $res['message'] = '等待用户扫描';
            return $res;
        }

        if (is_numeric($qr_value)) {
            // 说明用户已扫码，前台网页会继续轮询，等待用户确认
            // TODO：显示用户信息在网页
            $user_id = $qr_value;
            $user = Models\User::find($user_id);

            $res['data'] = [
                'user_name'     => $user->name,
                'user_avatar'   => $user->profile->avatar,
            ];
            $res['status'] = 202;
            $res['message'] = '用户已扫描<br>等待用户确认';
            return $res;

            // TODO：无权登录会在手机端登录，当然也可以显示在网页上（还是应该显示在手机上）
            // if (!$user->roleIs('author')) {
            //     abort(403, '您的角色无权登录这个系统');
            // }
        }

        if ($from == 'uhome') {
            // 验证格式
            preg_match('/^(\d+)_confirm$/', $qr_value, $matches);
            if (!$matches) {
                $res['status'] = 500;
                $res['message'] = '扫码异常<br>请重新刷新页面重扫';
                return $res;
            }

            // 取得用户信息
            $user_id = (int) $qr_value;
            $user = Models\User::find($user_id);

            // TODO：无权登录会在手机端登录，理论上不会走到这里
            if (!$user->roleIs('author')) {
                $res['status'] = 403;
                $res['message'] = '您的角色无权登录这个系统';
                return $res;
            }

            // 验证通过，跳转
            $res['data'] = [
                'confirm_pass'  => true,
            ];
            $res['status'] = 202;
            $res['message'] = '用户以确认<br>即将跳转';
        } else {
            // 从未知的系统登录
            $res['status'] = 403;
            $res['message'] = '您通过未知的系统登录';
        }
        
        return $res;
    }
}
