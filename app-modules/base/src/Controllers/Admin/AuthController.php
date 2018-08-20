<?php

namespace Modules\Base\Controllers\Admin;

use Exception;
use Session;
use Fanybook\LicenseFacade as License;

use Modules\Base\Models\User;
use Request;
use Validator;
use Auth;
use DB;
use Config;

class AuthController extends \BaseController
{
    public function __construct()
    {
        $this->middleware('admin.logined', ['only' => 'login']);
    }

    public function login()
    {
        return view('base::admin.auth.login');
    }

    public function logout()
    {
        Session::forget('url.intended');
        Auth::logout();
        return redirect(config('backpack.base.route_prefix', 'admin') . '/login');
    }

    public function postLogin()
    {
        try {
            $validator = Validator::make(Request::all(), [
                'email'    => 'required|email',
                'password' => 'required',
                'captcha'  => 'required',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first());
            }

            if (Request::input('captcha') !== Session::get('captcha')) {
                throw new Exception('验证码不正确，请重新输入');
            }

            $getUser = User::where('email', Request::input('email'))->first();

            if(!$getUser) {
                throw new Exception('用户不存在');
            }

            if (!$getUser->roleIs('admin') && !$getUser->roleIs('kuguan')) {
                abort(403, '您的角色无权登录这个系统');
            }

            $credentials = [
                'email'    => Request::input('email'),
                'password' => Request::input('password'),
            ];

            if (!Auth::attempt($credentials)) {
                throw new Exception('登录失败，密码不正确');
            }
            // if (Auth::guest()) {
            //     if (!Auth::attempt($credentials)) {
            //         throw new Exception('登录失败，密码不正确');
            //     }
            // } else {    // 此处有bug，如果已登陆但不是一个人，这里没有再登陆
            //     if (!Auth::validate($credentials)) {
            //         throw new Exception('登录失败，密码不正确');
            //     }
            // }

            Session::put('admin_auth', time());

            $result['result']  = true;
            $result['message'] = '登录成功，马上跳转';

            if (Session::get('url.intended')) {
                $result['goUrl'] = Session::remove('url.intended');
            } else {
                $result['goUrl'] = '/'.config('backpack.base.route_prefix', 'admin');
            }
        } catch (Exception $e) {
            $result['result']  = false;
            $result['message'] = $e->getMessage();
        }

        return $result;
    }
}
