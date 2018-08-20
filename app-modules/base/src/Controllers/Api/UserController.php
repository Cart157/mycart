<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Libraries\Captcha\Sms\SmsCaptcha;
use Modules\Base\Models;
use Carbon\Carbon;
use Request;
use Validator;
use Illuminate\Validation\Rule;
use JWTAuth;
use Hash;
use GuzzleHttp;

class UserController extends \BaseController
{
    //分页常量
    const LIMIT_PER_PAGE = 20;

    /**
     * 用户标签
     * @param $id
     * @return mixed
     */
    public function note($id)
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'note'        => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }
            // 业务逻辑
            $user = Models\UserProfile::findOrFail($id);
            $user->note = $user->note .';'. request('note');
            $user->save();

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $user = Models\User::search(Request::all());
            $user->each(function ($item) {
                $item->avatar = $item->profile->avatar;
                $item->bio = $item->profile->bio;
                unset($item->profile);

                $follow_status = \DB::table('circle_follow_user_log')
                                ->where('user_id', JWTAuth::user()->id)
                                ->where('target_id', $item->id)
                                ->first();

                $friend_status = \DB::table('circle_follow_user_log')
                                ->where('user_id', $item->id)
                                ->where('target_id', JWTAuth::user()->id)
                                ->first();

                $item->is_follow = (boolean) $follow_status;
                $item->is_friend = ((boolean) $follow_status) && ((boolean) $friend_status);
                $item->is_me = JWTAuth::user()->id == $item->id ? true : false;
            });

            $res['data'] = $user;

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function show($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $user = Models\User::with('profile')->findOrFail($id);
            $user->addHidden(['mobile', 'email', 'created_at', 'updated_at', 'deleted_at']);
            $res['data'] = $user;

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    // public function setting()
    // {
    //     return $this->update(JWTAuth::user()->id);
    // }

    public function update($id)
    {
        if ($id != JWTAuth::user()->id) {
            abort(500, '非法操作，不能更新');
        }

        $res = parent::apiCreatedResponse();

        $user_table = (new Models\User)->getTable();
        $validator = Validator::make(Request::all(), [
            'name'          => [
                'max:20',
                Rule::unique($user_table)->ignore($id),
            ],
            'email'         => 'max:50|unique:'.$user_table,
            //两次密码一致
            'password'      => 'min:6|max:15|confirmed',
            'avatar'        => 'url|max:255',
            'cover'         => 'url|max:255',
            'bio'           => 'max:150',
            'sex'           => 'in:0,1,2',
            'location_code' => 'digits:6',
        ], [
            'password.confirmed'        => '两次输入的密码不一致',
            'old_password.required'     => '请输入老密码',
        ]);

        $validator->sometimes(['old_password'], 'required', function ($input) {
            return Request::has('password');
        });

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 业务逻辑
            $user = Models\User::with('profile')->findOrFail($id);
            $user->addHidden(['created_at', 'updated_at', 'deleted_at']);

            if (Request::has('name')) {
                if (is_sensitive(Request::input('name'))) {
                    abort(400, '昵称包含敏感词，请修改后重试');
                }

                $user->name = Request::input('name');
            }
            if (Request::has('email')) {
                $user->email = Request::input('email');
            }
            if (Request::has('password')) {
//                // back: 代码备考（另一种验证方式）
//                $auth_ret = \Auth::validate(['id' => $user->id, 'password' => Request::input('old_password')]);
//                if (!$auth_ret) {
//                    abort(400, '老密码不正确');
//                }
                if (!Hash::check(Request::input('old_password'), $user->getAuthPassword())) {
                    abort(400, '老密码不正确');
                } elseif (Request::input('password') == Request::input('old_password')) {
                    abort(400, '新密码和老密码相同，没有变化');
                }

                $user->password = bcrypt(Request::input('password'));
            }

            // 上传头像  移动到七牛云的代码片段
            if (Request::has('avatar')) {
                // 移动七牛图片，然后把input的数据剔除域名
                $source = get_qiniu_key(Request::input('avatar'));

                $path_parts = pathinfo($source);
                $target = sprintf('/uploads/base/user/%d/avatar.%s', $user->id, $path_parts['extension']);

                move_qiniu_uploads($source, $target);

                $user->profile->avatar = $target . '?t=' . time();
            }

            // 上传封面
            if (Request::has('cover')) {
                // 移动七牛图片，然后把input的数据剔除域名
                $source = get_qiniu_key(Request::input('cover'));

                $path_parts = pathinfo($source);
                $target = sprintf('/uploads/base/user/%d/cover.%s', $user->id, $path_parts['extension']);

                move_qiniu_uploads($source, $target);

                $user->profile->cover = $target . '?t=' . time();
            }

            if (Request::has('bio')) {
                $user->profile->bio = Request::input('bio');
            }
            if (Request::has('sex')) {
                $user->profile->sex = Request::input('sex');
            }
            if (Request::has('location_code')) {
                $user->profile->location_code = Request::input('location_code');
                // TODO: 进一步验证code的有效性，转成文字保存location_name
                $user->profile->location_name = Request::input('location_code');
            }

            $user->save();
            $user->profile->save();
            $res['message'] = '设置成功';

            // 更新网易云ID的头像昵称
            $guzzle = new GuzzleHttp\Client;

            $accid      = $user->profile->im_user;//'bu-'. sprintf('%010s', $user->id);
            $nonce      = mt_rand(100000, 999999);
            $cur_time   = time();
            $check_sum  = sha1('2144fd0f6416' . $nonce . $cur_time);

            $user_info = [
                'accid' => $accid,
            ];

            if (Request::has('name')) {
                $user_info['name'] = Request::input('name');
            }
            if (Request::has('avatar')) {
                $user_info['icon'] = cdn(). $user->profile->avatar;
            }

            $response = $guzzle->post('https://api.netease.im/nimserver/user/updateUinfo.action', [
                'body'    => http_build_query($user_info),
                'headers' => [
                    'Content-Type'  => 'application/x-www-form-urlencoded;charset=utf-8',
                    'AppKey'        => 'd988edda82c87e01723014b7df8b031b',
                    'Nonce'         => $nonce,
                    'CurTime'       => $cur_time,
                    'CheckSum'      => $check_sum,
                ],
            ]);

            // $netease_res = json_decode((string) $response->getBody(), true);

            // if ($netease_res['code'] == '200') {
            //     $user_profile->im_user  = $accid;
            //     $user_profile->im_token = $netease_res['info']['token'];
            //     $user_profile->save();
            // }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }


    //绑定手机  绑定手机！！！！！！！！！！！！！！！
    public function bindMobile($id)
    {
        //传过来的id和本地的存储id不同提示非法操作
        if ($id != JWTAuth::user()->id) {
            abort(403, '非法操作');
        }

        //如果JWTAurh中有mobile信息 就提示已经绑定了
        if (JWTAuth::user()->profile->mobile) {
            abort(403, '已经绑定手机，暂时不支持换绑');
        }

        //初始化返回信息
        $res = parent::apiCreatedResponse();

        //验证器 验证接受到的参数
        $validator = Validator::make(Request::all(), [
            //手机号唯一
            'mobile'    => 'required|digits:11|unique:'.(new Models\UserProfile)->getTable(),
            //密码必须
            'password'  => 'required|min:6|max:15',
            //验证码必须
            'captcha'   => 'required',
        ]);

        try {
            //如果验证失败
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            //验证码  验证手机号
            $captcha = new SmsCaptcha();
            //验证输入的手机号和对应的验证码
            $captcha->validate(Request::input('mobile'), Request::input('captcha'));

            //获取本地的用户
            $user         = JWTAuth::user();
            $user_profile = JWTAuth::user()->profile;

            //加密密码的方法
            $user->password = bcrypt(Request::input('password'));
            $user_profile->mobile = Request::input('mobile');

            //user表保存信息
            $user->save();
            //profile表保存此信息
            $user_profile->save();

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }


    //绑定支付宝
    public function bindAlipay($id)
    {
        if ($id != JWTAuth::user()->id) {
            abort(403, '非法操作');
        }

        //初始化返回信息
        $res = parent::apiCreatedResponse();

        //构造验证器
        $validator = Validator::make(Request::all(), [
            'alipay_account'    => 'required',
            'alipay_realname'   => 'required',
            'mobile'            => 'required|digits:11',
            'password'          => 'required',
            'captcha'           => 'required',
        ], [
            'alipay_account.required'   => '请输入支付宝账号',
            'alipay_realname.required'  => '请输入支付宝账号的实名信息，以便打款时确认您的身份',
            'mobile.required'           => '手机号是必须项',
            'mobile.digits'             => '手机号的格式不正确',
            'password.required'         => '请输入您Ban帐号的密码，以验证您有操作权限',
            'captcha.required'          => '请输入验证码',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            //手机验证
            $captcha = new SmsCaptcha();
            $captcha->validate(Request::input('mobile'), Request::input('captcha'));

            $user         = JWTAuth::user();
            $user_profile = JWTAuth::user()->profile;

            if (!Hash::check(Request::input('password'), $user->getAuthPassword())) {
                abort(403, '密码错误');
            }
/*
            // 判断 支付宝号 是否被其他人绑定
            $is_exist = Models\UserProfile::where('alipay_account', Request::input('alipay_account'))
                                          ->where('user_id', '<>', $user->id)
                                          ->count();
            if ($is_exist) {
                abort(403, '支付宝号已经被其他人绑定');
            }
*/
            $user_profile->alipay_account  = Request::input('alipay_account');
            $user_profile->alipay_realname = Request::input('alipay_realname');
            $user_profile->save();

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function bindSocialite($id)
    {
        if ($id != JWTAuth::user()->id) {
            abort(403, '非法操作');
        }

        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'driver'    => ['required', Rule::in(['qq', 'wechat', 'weibo'])],
            'uid'       => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $user         = JWTAuth::user();
            $user_profile = JWTAuth::user()->profile;

            // 验证其他人是否已经绑定过这个号
            $is_exist = Models\UserProfile::where(Request::input('driver'), Request::input('uid'))
                                          ->where('user_id', '<>', $user->id)
                                          ->count();

            if ($is_exist) {
                abort(403, '这个帐号已经被其他用户绑定了');
            }

            // 验证自己是否绑定过这个字段
            if ($user_profile->{Request::input('driver')}) {
                abort(403, '您的帐号已经绑定过'. Request::input('driver') .'了');
            }

            // 验证用户进行保存
            $user_profile->{Request::input('driver')} = Request::input('uid');
            $user_profile->save();

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function statistics($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $user = Models\User::with('profile')->findOrFail($id);
            $res['data'] = [
                'moment_cnt'    => $user->moments->count(),
                'article_cnt'   => $user->articles->count(),
                'follow_topic_cnt'  => $user->follow_topics->count(),
                'follow_user_cnt'   => $user->follow_users->count(),
                'follow_fans_cnt'   => $user->follow_fans->count(),
                'coupon_cnt'    => \Modules\Activity\Models\Promotion\UserCoupon::where('user_id', $id)
                                                                  ->where('end_time', '>', Carbon::now())
                                                                  ->count(),
                'coin_num'      => $user->profile->coin_num,
            ];

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function moment($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $user = Models\User::findOrFail($id);
            $res['data'] = $user->moments;

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function article($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $user = Models\User::findOrFail($id);
            $res['data'] = $user->articles;

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function followTopic($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $user = Models\User::findOrFail($id);
            $res['data'] = $user->follow_topics;

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function followUser($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $user = Models\User::findOrFail($id);
            $res['data'] = $user->follow_users()
                                ->skip(self::LIMIT_PER_PAGE * (Request::input('page') - 1))
                                ->take(self::LIMIT_PER_PAGE)
                                ->orderBy('circle_follow_user_log.created_at', 'desc')
                                ->get()
                                ->each(function ($item) {
                $item->bio = $item->profile->bio;
                $item->avatar = $item->profile->avatar;
                $item->addHidden(['mobile', 'email', 'created_at', 'updated_at', 'deleted_at', 'profile', 'pivot']);

                $follow_status = \DB::table('circle_follow_user_log')
                                ->where('user_id', JWTAuth::user()->id)
                                ->where('target_id', $item->id)
                                ->first();

                $friend_status = \DB::table('circle_follow_user_log')
                                ->where('user_id', $item->id)
                                ->where('target_id', JWTAuth::user()->id)
                                ->first();

                $item->is_follow = (boolean) $follow_status;
                $item->is_friend = ((boolean) $follow_status) && ((boolean) $friend_status);
                $item->is_me = JWTAuth::user()->id == $item->id ? true : false;
            });

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function followFans($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $user = Models\User::findOrFail($id);
            $fans = $user->follow_fans();

            if (Request::has('date') && Request::input('date') == 'recent') {
                $recent = date('Y-m-d', strtotime('-15 day'));
                $fans->where('circle_follow_user_log.created_at', '>=', $recent);
                $recent_cnt = $fans->count();
                $res['extra'] = ['recent_cnt' => $recent_cnt];
            }

            $res['data'] = $fans->skip(self::LIMIT_PER_PAGE * (Request::input('page') - 1))
                                ->take(self::LIMIT_PER_PAGE)
                                ->orderBy('circle_follow_user_log.created_at', 'desc')
                                ->get()
                                ->each(function ($item) {
                $item->bio = $item->profile->bio;
                $item->avatar = $item->profile->avatar;
                $item->addHidden(['mobile', 'email', 'created_at', 'updated_at', 'deleted_at', 'profile', 'pivot']);

                $follow_status = \DB::table('circle_follow_user_log')
                                ->where('user_id', JWTAuth::user()->id)
                                ->where('target_id', $item->id)
                                ->first();

                $friend_status = \DB::table('circle_follow_user_log')
                                ->where('user_id', $item->id)
                                ->where('target_id', JWTAuth::user()->id)
                                ->first();

                $item->is_follow = (boolean) $follow_status;
                $item->is_friend = ((boolean) $follow_status) && ((boolean) $friend_status);
                $item->is_me = JWTAuth::user()->id == $item->id ? true : false;
            });

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function followStatus($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            $topic = Models\User::findOrFail($id);

            $follow_status = \DB::table('circle_follow_user_log')
                                ->where('user_id', JWTAuth::user()->id)
                                ->where('target_id', $id)
                                ->first();

            $friend_status = \DB::table('circle_follow_user_log')
                                ->where('user_id', $id)
                                ->where('target_id', JWTAuth::user()->id)
                                ->first();

            $res['data'] = [
                'is_follow'     => (boolean) $follow_status,
                'is_friend'     => ((boolean) $follow_status) && ((boolean) $friend_status),
                'is_me'         => JWTAuth::user()->id == $id ? true : false,
            ];

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function follow($id)
    {
        $res = parent::apiCreatedResponse();

        try {
            $topic = Models\User::findOrFail($id);

            if ($id == JWTAuth::user()->id) {
                abort(500, '非法操作，不能更新');
            }

            $follow_status = \DB::table('circle_follow_user_log')
                                ->where('user_id', JWTAuth::user()->id)
                                ->where('target_id', $id)
                                ->first();

            $friend_status = \DB::table('circle_follow_user_log')
                                ->where('user_id', $id)
                                ->where('target_id', JWTAuth::user()->id)
                                ->first();

            if ($follow_status) {
                \DB::table('circle_follow_user_log')
                   ->where('user_id', JWTAuth::user()->id)
                   ->where('target_id', $id)
                   ->delete();

                $res['message'] = '已取消关注';
            } else {
                \DB::table('circle_follow_user_log')
                   ->insert([
                        'user_id'   => JWTAuth::user()->id,
                        'target_id' => $id,
                        'created_at'=> new Carbon(),
                    ]);

                $res['message'] = '关注成功';
            }

            $res['data'] = [
                'is_follow'     => ! (boolean) $follow_status,
                'is_friend'     => (! (boolean) $follow_status) && ((boolean) $friend_status),
            ];

            if ($res['data']['is_follow']) {
                system_notice($id, [
                    "type"      => "user-follow",
                    "message"   => '关注了你',
                ], JWTAuth::user());
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function friend($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            $user = Models\User::findOrFail($id);

            if ($id != JWTAuth::user()->id) {
                abort(500, '非法操作!');
            }

            $friends = $user->follow_users()->whereHas('follow_users', function($query) use($id) {
                $query->where('target_id', $id);
            });

            if (Request::has('wd')) {
                $friends->where('name', 'like', '%'.Request::input('wd').'%');
            }

            $limit = Request::has('limit') ? Request::input('limit') : self::LIMIT_PER_PAGE;
            $friends = $friends->paginate($limit)->items();

            $res['data'] = array_map(function ($item) {
                $item->bio = $item->profile->bio;
                $item->avatar = $item->profile->avatar;
                $item->setVisible(['id', 'name', 'bio', 'avatar']);

                return $item;
            }, $friends);

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function inviter()
    {
        $res = parent::apiFetchedResponse();

        $validator = Validator::make(Request::all(), [
            'ic'        => 'required',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 通过ic取得用户
            $user = Models\User::where('invitation_code', Request::input('ic'))->first();
            if (!$user) {
                abort(403, '该邀请已失效');
                // XXX:更好的是仍然邀请，但把邀请者改了，但不认识的人邀请成功率也不会高
            }

            $res['data'] = [
                'name'      => $user->name,
                'avatar'    => $user->profile->avatar,
            ];

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function invitation()
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'mobile'    => ['required', 'digits:11', 'regex:/1[345678]{1}\d{9}/'],
            'ic'        => 'required',
        ], [
            'mobile.required' => '手机号必须填写',
            'mobile.digits'   => '手机号格式不正确',
            'mobile.regex'    => '手机号格式不正确',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 判断手机是否已经注册过了
            $is_exist = Models\User::whereHas('profile', function($query) {
                $query->where('mobile', Request::input('mobile'));
            })->count();

            if ($is_exist) {
                abort(403, '该手机号已经注册过了');
            }

            // 判断手机是否已被邀请过了
            $invitation = Models\UserInvitation::where('new_mobile', Request::input('mobile'))->first();
            if ($invitation) {
                if ($invitation->created_at > Carbon::now()->subDays(7)) {
                    abort(403, '该手机号已经被邀请过了');
                } else {
                    $invitation->delete();
                }
            }

            // 通过ic取得用户
            $user = Models\User::where('invitation_code', Request::input('ic'))->first();

            Models\UserInvitation::create([
                'user_id' => $user ? $user->id : 0,
                'new_mobile' => Request::input('mobile'),
            ]);

            $res['message'] = '红包已发送，请去app注册领取';

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }


    /**
     * 提现申请
     * @param $cash_amount  申请金额
     */
    public function cashApply()
    {
        $res = parent::apiCreatedResponse();
        $validator = Validator::make(Request::all(),[
            'cash_amount' => 'required|integer|min:0'
        ]);
        try{

            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }
            /*
             * 1.生成单号
             * 2.查询支付宝账号
             * 3.确定状态 10.默认,1.完成支付,0.拒绝
             */
            $user_id  = JWTAuth::user()->id;

            do {
                $cash_sn = date('YmdHi',time()).mt_rand(0, 999);
                $sn = Models\UserCash::where('cash_sn',$cash_sn)->first();
            } while ($sn);

            $alipay = Models\UserProfile::where('user_id',$user_id)
                    ->select('alipay_account','alipay_realname')
                    ->first();
            Models\UserCash::create([
                'user_id'         => $user_id,
                'cash_sn'         => $cash_sn,
                'cash_amount'     => Request::input('cash_amount'),
                'alipay_account'  => $alipay->alipay_account,
                'alipay_realname' => $alipay->alipay_realname
            ]);

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }
        return $res;
    }

    public function queryQrCode()
    {
        # 初始化返回信息
        $res = parent::apiFetchedResponse();

        # laravel验证
        $validator = Validator::make(Request::all(), [
            'qr_code'    => 'required',
        ], [
            'qr_code.required' => '',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $user_id = Models\User::getUserIdByQrCode(Request::input('qr_code'));

            $res['data']['user_id'] = $user_id;
            if (!$user_id) {
                abort(404, '没有该用户信息');
            }
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

}
