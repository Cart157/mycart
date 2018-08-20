<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Libraries\Captcha\Sms\SmsCaptcha;
use Modules\Base\Models;
use Modules\Activity\Models\Promotion;
use Illuminate\Filesystem\Filesystem;
use GuzzleHttp;
use Request;
use Validator;
use Illuminate\Validation\Rule;
use Hash;
use Carbon\Carbon;
use JWTAuth;

class AuthController extends \BaseController
{
    public function login()
    {
        $res = parent::apiCreatedResponse();

        // todo:验证码
        $validator = Validator::make(Request::all(), [
            'mobile'    => 'required|digits:11',
            'password'  => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 返回token
//            $http = new GuzzleHttp\Client;
//            $response = $http->post('http://bulv.dev/oauth/token', [
//                'form_params' => [
//                    'grant_type' => 'password',
//                    'client_id' => '4',
//                    'client_secret' => 'Bkpm1reBXXYNgWzECI1ZxLexiPgaCS5aBCQ3QV5Y',
//                    'username' => Request::input('mobile'),
//                    'password' => Request::input('password'),
//                    'scope' => '*',
//                ],
//            ]);
//            $token_info = json_decode((string) $response->getBody(), true);

           /* $user = Models\User::whereHas('profile', function ($query) {
                $query->where('mobile', Request::input('mobile'));
            })->with('profile')->with('custom')->with(['wallet'])->first();*/

            $user = Models\User::whereHas('profile', function ($query) {
                    $query->where('mobile', Request::input('mobile'));
                })
                ->with('profile')
                ->with('custom_design')
                ->with('appraiser')
                ->with(['wallet' => function ($query) {
                    $query->select('user_id', 'truename', 'pay_password_checked','alipay_checked');
                }])
                ->first();


            // $em_user = Models\User::where('email', Request::input('mobile'))->with('profile')->first();

            // if (!$user && $em_user) {
            //     $user = $em_user;
            // } else
            if (!$user) {
                abort(401, '该手机号未注册');
            }

            if (!$user->password) {
                abort(403, '该帐号密码为空，请先去设置密码');
            }

            if (!Hash::check(Request::input('password'), $user->getAuthPassword())) {
                abort(401, '帐号密码不匹配');
            }

            $banned = Models\UserBanned::where('user_id', $user->id)->first();
            if ($banned) {
                abort(401, $banned->reason);
            }

            // XXX:通过batch生成
            // # 20180531 为新增字段 serial_number 添加数据
            // if (empty($user->serial_number)) {
            //     $user->qr_code = Models\User::makeQrCode();
            //     $user->save();
            // }
            // # serial_number 添加数据结束

            // 整理返回数据
            $user->addHidden(['created_at', 'updated_at', 'deleted_at']);

            $res['data'] = [
                'user_info'     => $user,
                'token'         => JWTAuth::fromUser($user),
                'token_exp_in'  => config('jwt.ttl') * 60,
            ];

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function register()
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'mobile'    => 'required|unique:'.(new Models\UserProfile)->getTable(),
            'password'  => 'required|min:6|max:15',
            'captcha'   => 'required',
            'prefix'    => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $captcha = new SmsCaptcha();
            $captcha->validate(Request::input('mobile'), Request::input('captcha'));

            // // 业务逻辑
            // $new_user = [
            //     'password' => bcrypt(Request::input('password')),
            // ];

            // do {
            //     // 分配邀请码
            //     $invitation_code = str_rand(6);
            //     $new_user['invitation_code'] = $invitation_code;

            //     $is_exist = Models\User::where('invitation_code', $invitation_code)->count();
            // } while ($is_exist);

            // if (Request::has('name')) {
            //     $new_user['name'] = Request::input('name');
            // } else {
            //     $new_user['name'] = 'new_' . str_random(10);
            //     // $new_user['name'] = preg_replace('/(\d{3})(\d{4})(\d{4})/', '$1****$3', Request::input('mobile'));
            // }

            // if (Request::has('email')) {
            //     $new_user['email'] = Request::input('email');
            // }

            $user = Models\User::makeUser([
                'mobile'        => Request::input('mobile'),
                'mobile_prefix' => Request::input('prefix'),
                'password'      => Request::input('password'),
            ]);

            // $user = Models\User::create($new_user);

            // #给新增用户分配唯一序列号
            // $user->serial_number = $this->_make_serial_number($user->id);
            // $user->save();
            // #生成序列号完毕

            // $user_profile = new Models\UserProfile();
            // $user_profile->user_id  = $user->id;

            // $user_profile->prefix = Request::input('prefix');
            // $user_profile->cover    = '';
            // $user_profile->mobile   = Request::input('mobile');
            // $user_profile->coin_num = 800;
            // $user_profile->save();

            // Models\UserCoinLog::create([
            //     'user_id'   => $user->id,
            //     'change_num'=> 800,
            //     'get_way_id'=> 7,
            // ]);

            // 返回token
//            $http = new GuzzleHttp\Client;
//            $response = $http->post('http://bulv.dev/oauth/token', [
//                'form_params' => [
//                    'grant_type' => 'password',
//                    'client_id' => '4',
//                    'client_secret' => 'Bkpm1reBXXYNgWzECI1ZxLexiPgaCS5aBCQ3QV5Y',
//                    'username' => Request::input('mobile'),
//                    'password' => Request::input('password'),
//                    'scope' => '*',
//                ],
//            ]);
//            $token_info = json_decode((string) $response->getBody(), true);

            // 1.创建LeanCloud的IM帐号
            // LeanCloud\Client::initialize("G4MB1gQCUt6FbkS7X0Omwfmw-gzGzoHsz", "64MfGsIifAp7HEn63dVGWtgv", "8znieqKl3a8qXfurvcElLWIF");
            // $lc_user = new LeanCloud\User();
            // $lc_user->setUsername("alice");
            // $lc_user->setEmail("alice@example.net");
            // $lc_user->setPassword("passpass");
            // $lc_user->signUp();
            //
            // // 注册成功后，用户被自动登录。可以通过以下方法拿到当前登录用户和
            // // 授权码。
            // $lc_user  = LeanCloud\User::getCurrentUser();
            // $lc_token = LeanCloud\User::getCurrentSessionToken();

            // // 2.创建环信IM帐号
            // $easemob_uname = 'bu-'. substr(base64_encode(sprintf('%010s', $user->id)), 0, -2);
            // $easemob_token = $this->_getEasemobToken();
            // $guzzle = new GuzzleHttp\Client;

            // $response = $guzzle->post('http://a1.easemob.com/1153171102115530/zoom/users', [
            //     'body'    => '[
            //         {
            //             "username": "' .$easemob_uname. '",
            //             "password": "nihao_shijie"
            //         }
            //     ]',
            //     'headers' => [
            //         'Content-Type' => 'application/json',
            //         'Accept'       => 'application/json',
            //         'Authorization'=> 'Bearer '. $easemob_token['access_token'],
            //     ],
            // ]);
            // $easemob_user = json_decode((string) $response->getBody(), true);
            // if (isset($easemob_user['entities'])) {
            //     $user_profile->im_user = $easemob_uname;
            //     $user_profile->save();

            //     $user->im_user = $easemob_uname;
            // }

            // // 3.创建网易云IM帐号
            // $guzzle = new GuzzleHttp\Client();

            // $accid      = 'bu-'. sprintf('%010s', $user->id);
            // $nonce      = mt_rand(100000, 999999);
            // $cur_time   = time();
            // $check_sum  = sha1('2144fd0f6416' . $nonce . $cur_time);

            // $data = [
            //     'accid' => $accid,
            //     'name'  => $user->name,
            // ];

            // $response = $guzzle->post('https://api.netease.im/nimserver/user/create.action', [
            //     'body'    => http_build_query($data),
            //     'headers' => [
            //         'Content-Type'  => 'application/x-www-form-urlencoded;charset=utf-8',
            //         'AppKey'        => 'd988edda82c87e01723014b7df8b031b',
            //         'Nonce'         => $nonce,
            //         'CurTime'       => $cur_time,
            //         'CheckSum'      => $check_sum,
            //     ],
            // ]);

            // $netease_res = json_decode((string) $response->getBody(), true);

            // if ($netease_res['code'] == '200') {
            //     $user_profile->im_user  = $accid;
            //     $user_profile->im_token = $netease_res['info']['token'];
            // }

            // // $accid      = 'bu-'. sprintf('%010s', $user->id);
            // // $nonce      = mt_rand(100000, 999999);
            // // $cur_time   = time();
            // // $check_sum  = sha1('2144fd0f6416' . $nonce . $cur_time);

            // // 创建个人专属客服群
            // $data = [
            //     'tname' => '个人专属客服',
            //     'owner' => $accid,
            //     'members'   => '[]',
            //     'msg'       => '个人专属客服群-邀您进入',
            //     'magree'    => 0,
            //     'joinmode'  => 2,
            //     'icon'      => '',
            //     'beinvitemode'  => 1,
            //     'invitemode'    => 1,
            // ];

            // $response = $guzzle->post('https://api.netease.im/nimserver/team/create.action', [
            //     'body'    => http_build_query($data),
            //     'headers' => [
            //         'Content-Type'  => 'application/x-www-form-urlencoded;charset=utf-8',
            //         'AppKey'        => 'd988edda82c87e01723014b7df8b031b',
            //         'Nonce'         => $nonce,
            //         'CurTime'       => $cur_time,
            //         'CheckSum'      => $check_sum,
            //     ],
            // ]);

            // $netease_res = json_decode((string) $response->getBody(), true);

            // if ($netease_res['code'] == '200') {
            //     $user_profile->im_staff_tid = $netease_res['tid'];
            // }

            // $user_profile->save();

            // 发放邀请的礼物
            $invitation = Models\UserInvitation::where('new_mobile', Request::input('mobile'))
                                               ->where('is_activated', 0)
                                               ->where('created_at', '>', Carbon::now()->subDays(7))
                                               ->first();

            if ($invitation) {
                // 给 $invitation->user_id 发金币，如果用户还在的话
                $inviter = Models\User::find($invitation->user_id);
                if ($inviter) {
                    $inviter->profile->coin_num = $inviter->profile->coin_num + 200;
                    $inviter->profile->save();

                    Models\UserCoinLog::create([
                        'user_id'   => $invitation->user_id,
                        'change_num'=> 200,
                        'get_way_id'=> 2,
                    ]);

                    // 被邀请人默认关注邀请人
                    \DB::table('circle_follow_user_log')->insert([
                        'user_id'   => $user->id,
                        'target_id' => $inviter->id,
                        'created_at'=> new Carbon(),
                    ]);
                }

                // 给 $user 发98元红包，有800金币，所有人都有
                $coupon_tpls = Promotion\Coupon::whereIn('slug', ['OVEROFF_50', 'OVEROFF_30', 'OVEROFF_10'])
                                               ->get();

                foreach ($coupon_tpls as $coupon_tpl) {
                    // 生成用户的优惠券
                    Promotion\UserCoupon::makeUserCouponByTpl(
                        $user->id,
                        $coupon_tpl
                    );
                }

                $invitation->is_activated = 1;
                $invitation->save();
            }

            // 整理返回数据
            $user->addHidden(['created_at', 'updated_at']);
            $user->profile;

            $res['data'] = [
                'user_info'     => $user,
                'token'         => JWTAuth::fromUser($user),
                'token_exp_in'  => config('jwt.ttl') * 60,
            ];
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function checkMobile()
    {
        $res = parent::apiFetchedResponse();

        $validator = Validator::make(Request::all(), [
            'mobile'    => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $user = Models\UserProfile::where('mobile', Request::input('mobile'))->first();
            if ($user) {
                $res['data'] = [
                    'exist'     => true,
                ];
                $res['message'] = '已被注册';
            } else {
                $res['data'] = [
                    'exist'     => false,
                ];
                $res['message'] = '未被注册';
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function resetPassword()
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'mobile'    => 'required',
            'password'  => 'required',
            'captcha'   => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $captcha = new SmsCaptcha();
            $captcha->validate(Request::input('mobile'), Request::input('captcha'));

            // 业务逻辑
            $user = Models\User::whereHas('profile', function ($query) {
                $query->where('mobile', Request::input('mobile'));
            })->with('profile')->first();

            $user->password = bcrypt(Request::input('password'));
            $user->save();

            $res['message'] = '密码重置成功';

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function refreshToken()
    {
        $res = parent::apiFetchedResponse();

        try {
            $token = JWTAuth::parseToken()->refresh();

            $res['data'] = [
                'token'         => $token,
                'token_exp_in'  => config('jwt.ttl') * 60,
            ];
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function socialite()
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'driver'    => ['required', Rule::in(['qq', 'wechat', 'weibo'])],
            'uid'       => 'required|string',
            'nickname'  => 'required|string',
            'avatar'    => 'required|url',
            'sex'       => 'required|in:0,1,2',
        ]);

        $validator->sometimes('openid', ['required', 'string'], function ($input) {
            return $input->driver == 'wechat';
        });

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            if (Request::input('openid')) {
                $ori_uid = Request::input('uid');
                Request::merge(['uid' => Request::input('openid')]);
            }

            // 判断是否有用户已经绑定了这个第三方帐号
            $user = Models\User::whereHas('profile', function ($query) {
                $query->where(Request::input('driver'), Request::input('uid'));
            })->with('profile')->first();

            if (!$user) {
                abort(401, '该第三方账号未注册');
            }

            if (Request::input('openid')) {
                // 上面用老的去找找，找到了就换绑，下次呢？
                $user->profile->wechat = $uid;
            }

            if (!$user->profile->mobile) {
                abort(403, '该第三方账号未绑定手机号');
            }

            // 整理返回数据
            $user->addHidden(['created_at', 'updated_at']);

            $res['data'] = [
                'user_info'     => $user,
                'token'         => JWTAuth::fromUser($user),
                'token_exp_in'  => config('jwt.ttl') * 60,
            ];

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    // public function wxMiniapp()
    // {
    //     $res = parent::apiCreatedResponse();

    //     $validator = Validator::make(Request::all(), [
    //         'loginData'     => 'required',
    //         'userInfoData'  => 'required',
    //     ]);

    //     try {
    //         if ($validator->fails()) {
    //             abort(400, $validator->errors()->first());
    //         }

    //         $http = new GuzzleHttp\Client(['verify' => false]);
    //         $response = $http->get('https://api.weixin.qq.com/sns/jscode2session', [
    //             'query' => [
    //                 'appid'     => 'wx51cb4be4ce4967e6',
    //                 'secret'    => 'dc2def1f86f000b87215bd01c34d7255',
    //                 'js_code'   => Request::input('loginData.code'),
    //                 'grant_type'=> 'authorization_code',
    //             ],
    //         ]);
    //         $jscode2session = json_decode((string) $response->getBody(), true);

    //         if (isset($jscode2session['unionid'])) {
    //             $uid = $jscode2session['unionid'];
    //         } elseif (isset($jscode2session['session_key'])) {
    //             $ret_json = wx_miniapp_decrypt(
    //                 'wx51cb4be4ce4967e6',
    //                 $jscode2session['session_key'],
    //                 Request::input('userInfoData.encryptedData'),
    //                 Request::input('userInfoData.iv')
    //             );

    //             $ret_data = json_decode($ret_json, true);
    //             $uid = $ret_data['unionId'];
    //         }

    //         if (!isset($uid)) {
    //             \Log::info('小程序登录错误');
    //             \Log::error(Request::all());
    //             \Log::error($jscode2session);
    //         }

    //         // 解密出 unionId 然后判断是否注册
    //         $user = Models\User::whereHas('profile', function($query) use($uid) {
    //             $query->where('wechat', $uid);
    //         })->with('profile')->first();

    //         if (!$user) {
    //             // abort(401, '该第三方账号未注册');
    //             // 注册新的帐号
    //             $user_info = [
    //                 'name'      => Request::input('userInfoData.userInfo.nickName'),
    //                 'driver'    => 'wechat',
    //                 'uid'       => $uid,
    //                 'avatar'    => Request::input('userInfoData.userInfo.avatarUrl'),
    //                 'sex'       => Request::input('userInfoData.userInfo.gender'),
    //             ];

    //             $user = Models\User::makeUser($user_info);
    //         }

    //         // if (!$user->profile->mobile) {
    //         //     // abort(403, '该第三方账号未绑定手机号');
    //         //     // 直接放行，小程序不用绑手机，如app登录时自然会要求绑手机
    //         // }

    //         // 整理返回数据
    //         $user->addHidden(['created_at', 'updated_at']);
    //         $user->profile;

    //         $res['data'] = [
    //             'user_info'     => $user,
    //             'token'         => JWTAuth::fromUser($user),
    //             'token_exp_in'  => config('jwt.ttl') * 60,
    //         ];

    //     } catch (\Exception $e) {
    //         $res = parent::apiException($e, $res);
    //         $res['trace'] = $e->getTrace();
    //     }

    //     return $res;
    // }

    public function bindMobile()
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'mobile'    => ['required',  'regex:/1[345678]{1}\d{9}/'],
            'captcha'   => 'required',
            'driver'    => ['required', Rule::in(['qq', 'wechat', 'weibo'])],
            'uid'       => 'required',
            'nickname'  => 'required',
            'avatar'    => 'required|url',
            'sex'       => 'required|in:0,1,2',
            'confirm'   => 'in:1',
        ], [
            'mobile.required' => '手机号必须填写',
            'mobile.digits'   => '手机号格式不正确',
            'mobile.regex'    => '手机号格式不正确',
        ]);

        $isFromMiniapp = Request::input('driver') == 'wechat' && str_contains(Request::header('user-agent'), 'MicroMessenger');

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            if (!Request::input('confirm')) {
                $captcha = new SmsCaptcha();
                $captcha->validate(Request::input('mobile'), Request::input('captcha'));
            } else {
                // 验证cache了的captcha
            }

            // 手机账号
            $mb_user = Models\User::whereHas('profile', function ($query) {
                $query->where('mobile', Request::input('mobile'));
            })->with('profile')->first();

            // 三方账号
            $th_user = Models\User::whereHas('profile', function ($query) {
                $query->where(Request::input('driver'), Request::input('uid'));
            })->with('profile')->first();

            // 无手机号，无三方号
            if (!$mb_user && !$th_user) {
                $user_info = Request::only([
                    'mobile', 'driver', 'uid', 'avatar', 'sex',
                ]);

                $user_info['name'] = Request::input('nickname');

                $user = Models\User::makeUser($user_info);

            // 无手机号，有三方号
            } elseif (!$mb_user && $th_user) {
                $th_user->profile->mobile = Request::input('mobile');
                $th_user->profile->save();

                $user = $th_user;

            // 有手机号，没有三方号
            } elseif ($mb_user && !$th_user) {
                if ($isFromMiniapp) {
                    // 如果是小程序，保存到新的字段
                    $mb_user->profile->wechat_new = Request::input('uid');
                    $mb_user->profile->save();

                    $user = $mb_user;
                } else {
                    // 这种情况，但是手机号有绑定
                    if (!$mb_user->profile->{Request::input('driver')}) {
                        $mb_user->profile->{Request::input('driver')} = Request::input('uid');
                        $mb_user->profile->save();

                    // 绑了三方的情况
                    } else {
                        // 绑了三方，但却不是这个三方（因为没有三方号）
                        $opt = [
                            'qq'        => 'QQ',
                            'wechat'    => '微信',
                            'weibo'     => '微博',
                        ];

                        if (!Request::input('confirm')) {
                            abort(403, '此手机号已经绑定了其他“'. $opt[Request::input('driver')] .'”');
                        }
                    }

                    $user = $mb_user;
                }

            // 有手机号，也有三方号
            } else {
                if ($isFromMiniapp) {
                    // 如果是小程序，保存到新的字段
                    $mb_user->profile->wechat_new = Request::input('uid');
                    $mb_user->profile->save();

                    $user = $mb_user;
                } else {
                    // 这种情况，肯定帐号有分裂，取(手机号)舍(三方号)一下
                    if (!$mb_user->profile->{Request::input('driver')}) {
                        $mb_user->profile->{Request::input('driver')} = Request::input('uid');
                        $mb_user->profile->save();

                        $th_user->profile->{Request::input('driver')} = 'mv_'. $mb_user->id;
                        $th_user->profile->save();

                    // 绑了三方的情况
                    } else {
                        // 这里包含了未分裂的情况
                        // 绑了三方，但却不是这个三方
                        $opt = [
                            'qq'        => 'QQ',
                            'wechat'    => '微信',
                            'weibo'     => '微博',
                        ];

                        if ($mb_user->id == $th_user->id) {
                            abort(403, '此手机号已经绑定了这个“'. $opt[Request::input('driver')] .'”，无需重复绑定');
                        }

                        if (!Request::input('confirm')) {
                            abort(403, '此手机号已经绑定了其他“'. $opt[Request::input('driver')] .'”');
                        }
                    }

                    $user = $mb_user;
                }
            }

            // 整理返回数据
            $user->addHidden(['created_at', 'updated_at']);
            $user->profile;

            $res['data'] = [
                'user_info'     => $user,
                'token'         => JWTAuth::fromUser($user),
                'token_exp_in'  => config('jwt.ttl') * 60,
            ];

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    // private function _getEasemobToken()
    // {
    //     $guzzle = new GuzzleHttp\Client;

    //     $response = $guzzle->post('http://a1.easemob.com/1153171102115530/zoom/token', [
    //         'body'    => '{
    //            "grant_type": "client_credentials",
    //            "client_id": "YXA6MYrvUL_JEeevoyuaCQLTGA",
    //            "client_secret": "YXA6NySopksBxn3RhosutMAaV7dqc3E"
    //         }',
    //         'headers' => [
    //             'Content-Type' => 'application/json',
    //             'Accept'       => 'application/json',
    //         ],
    //     ]);

    //     return json_decode((string) $response->getBody(), true);
    // }

    // /**
    //  * 201805311509
    //  * Author: zhanglei
    //  * 通过 user表中 serial_number 获取用户的 user表中 id, 主要扫描二维码使用
    //  * @params serial_number 用户编号
    //  * @URL POST：api/qrcode/userid
    //  */
    // public function getUserBySerialNumber()
    // {
    //     # 初始化返回信息
    //     $res = parent::apiFetchedResponse();

    //     # laravel验证
    //     $validator = Validator::make(Request::all(), [
    //         'serial_number'    => 'required',
    //     ],[
    //         'serial_number.required' => "亲，需要唯一编号"
    //     ]);

    //     try {
    //         if ($validator->fails()) {
    //             return error_json(400, $validator->errors()->first());
    //         }
    //         # 接受用户编号
    //         $serial_number = Request::input('serial_number');
    //         # 调用模型方法查询用户id

    //         $user = Models\User::getUserBySerialNumber($serial_number);

    //         if( $user ){
    //             $res['data']['user_id'] = $user;
    //         }else{
    //             return error_json(200, "没有该用户信息");
    //         }
    //     } catch (\Exception $e) {
    //         $res = parent::apiException($e, $res);
    //         dd(13);
    //     }

    //     return $res;
    // }


//-----辅助方法--------------------------------------------------------------------------------
    // /**
    //  * 创建用户的唯一序列号
    //  *  规则：id号(最长5位，过长截取) + 8位日期 + 4位随机数 -- 7201805318874
    //  */
    // protected function _make_serial_number($user_id){
    //     #如果 user_id 超过5位，截取
    //     $user_id = intval($user_id) > 99999 ? substr( (string)$user_id, 0, 5 ) : (string)$user_id;
    //     #获取日期
    //     $day = date("Ymd", time());
    //     #获取4位随机数
    //     $rand_number = mt_rand(1000, 9999);
    //     #生成serial_number
    //     $serial_number = $user_id . $day . $rand_number;

    //     #返回序列号
    //     return $serial_number;
    // }

    public function qrLoginScan()
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'uuid'    => 'required',
            'from'    => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $user = JWTAuth::user();
            $uuid = Request::input('uuid');
            $from = Request::input('from');

            $qr_value = cache('qrlogin_'. $uuid);

            if (is_null($qr_value)) {
                abort(403, '二维码已过期，请重新刷新页面');
            }

            if ($qr_value != 0) {
                abort(403, '二维码已失效，请重新刷新页面');
            }

            if ($from == 'uhome' && !$user->roleIs('author')) {
                abort(403, '您的角色无权登录这个系统');
            }

            // 存入user_id
            cache(['qrlogin_'. $uuid => $user->id], 2);

            $res['message'] = '请确认登录';
            $res['data'] = [
                'user_name'     => $user->name,
                'user_avatar'   => $user->profile->avatar,
            ];

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function qrLoginConfirm()
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'uuid'    => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $user = JWTAuth::user();
            $uuid = Request::input('uuid');

            $qr_value = cache('qrlogin_'. $uuid);

            if (is_null($qr_value)) {
                abort(403, '二维码已失效，请重新刷新扫描');
            }

            if ($qr_value == 0) {
                abort(403, '二维码还未被扫描，请先扫描');
            }

            if ($qr_value != $user->id) {
                preg_match('/^(\d+)_confirm$/', $qr_value, $matches);
                if ($matches && $matches[1] == $user->id) {
                    abort(403, '您已经确认过了，请勿重复提交');
                }

                abort(403, '二维码已被其他用户扫描，请重新刷新扫描');
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
