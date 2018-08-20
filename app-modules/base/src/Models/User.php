<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Modules\Mall\Models as MallModels;
use Modules\Customization\Models as CustomizationModels;
use Modules\Care\Models as CareModels;
use DB;

class User extends Authenticatable implements JWTSubject
{
    use CrudTrait;
    use Notifiable;

    const LIMIT_PER_PAGE = 10;

    protected $table = 'base_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'invitation_code'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'pivot'
    ];

    //订制师
    public function custom_design()
    {
        return $this->hasOne('Modules\Customization\Models\CustomDesign','user_id','id');
    }

    // 用户详细信息
    public function profile()
    {
        return $this->hasOne('Modules\Base\Models\UserProfile');
    }
    //鉴定师
    public function appraiser()
    {
        return $this->hasOne('Modules\Appraisal\Models\Appraiser', 'user_id');
    }

    public function author()
    {
        return $this->hasOne('Modules\Cms\Models\Author', 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany('Modules\Base\Models\RbacRole', 'base_rbac_user_role_mst', 'user_id', 'role_id');
    }

    //圈子评论
    public function moments()
    {
        return $this->hasMany('Modules\Circle\Models\Moment');
    }

    //达人
    public function talent()
    {
        return $this->hasOne('Modules\Base\Models\Talent', 'user_id');
    }

    //用户和钱包的关系是一对一
    public function wallet(){
        return $this->hasOne('Modules\Base\Models\Wallet', 'user_id');
    }

    // 圈子的群组而非用户组
    // 如果模块可插拔，是否可以用 trait 引入
    public function groups()
    {
        return $this->belongsToMany('Modules\Circle\Models\Group', 'circle_group_member_mst', 'user_id', 'group_id');
    }

    // public function articles()
    // {
    //     return $this->hasMany('Modules\Cms\Models\Article');
    // }

    //圈子我关注的人
    public function follow_users()
    {
        return $this->belongsToMany('Modules\Base\Models\User', 'circle_follow_user_mst', 'user_id', 'target_id')
                    ->with('profile');
    }
    //圈子我的粉丝
    public function follow_fans()
    {
        return $this->belongsToMany('Modules\Base\Models\User', 'circle_follow_user_mst', 'target_id', 'user_id')
                    ->with('profile');
    }


    /**
     * 制作一个新用户
     * @param  array    $user_info      用户信息
     * @return object                   新用户的对象
     */
    public static function makeUser($user_info)
    {
        $invitation_code = self::makeInvitationCode();

        // 事务开始
        DB::beginTransaction();

        try {
            $user = new self();
            $user->invitation_code = self::makeInvitationCode();
            $user->qr_code         = self::makeQrCode();

            if (isset($user_info['name'])) {
                // 查重名
                $exists = self::where('name', 'like binary', $user_info['name'])->count();

                // 重名时加后缀
                if ($exists) {
                    $user->name = $user_info['name'] .'_'. str_rand(4);
                } else {
                    $user->name = $user_info['name'];
                }

            // 没提供名字时，生成临时名
            } else {
                // 这个也会重名
                do {
                    $tmp_name = 'new_'. str_rand(10);
                    $exists = self::where('name', $tmp_name)->count();
                } while ($exists);

                $user->name = $tmp_name;
            }

            if (isset($user_info['password'])) {
                $user->password = bcrypt($user_info['password']);
            }

            if (isset($user_info['email'])) {
                $user->password = $user_info['email'];
            }

            $user->save();
            $user_profile = new UserProfile();

            // 头像
            if (isset($user_info['avatar'])) {
                // 远程下载头像
                if (starts_with($user_info['avatar'], 'http')) {
                    $filesystem  = new \Illuminate\Filesystem\Filesystem();

                    $avatar_dir = sprintf('%s/uploads/base/user/%d', public_path(), $user->id);
                    $avatar_path = sprintf('%s/%s.jpg', $avatar_dir, uniqid('avatar_'));

                    if (!$filesystem->exists($avatar_dir)) {
                        $filesystem->makeDirectory($avatar_dir, 0755, true);
                    }

                    $client = new \GuzzleHttp\Client(['verify' => false]);
                    $response = $client->get($user_info['avatar'], ['save_to' => $avatar_path]);

                    // 取网站绝对路径保存
                    $user_profile->avatar = str_replace(public_path(), '', $avatar_path);

                // 直接保存
                } else {
                    $user_profile->avatar = $user_info['avatar'];
                }
            }

            if (isset($user_info['sex'])) {
                $user_profile->sex = $user_info['sex'];
            }

            if (isset($user_info['mobile'])) {
                $user_profile->mobile = $user_info['mobile'];
                $user_profile->prefix = isset($user_info['prefix']) ? $user_info['prefix'] : '86';
            }

            if (isset($user_info['uid'])) {
                $isFromMiniapp = \Request::input('driver') == 'wechat' && str_contains(\Request::header('user-agent'), 'MicroMessenger');

                if ($isFromMiniapp) {
                    $user_profile->wechat_new = $user_info['uid'];
                } else {
                    $user_profile->{$user_info['driver']} = $user_info['uid'];
                }
            }

            // 发放新人金币，生成金币获得记录
            $user_profile->coin_num = 800;
            UserCoinLog::create([
                'user_id'   => $user->id,
                'change_num'=> 800,
                'get_way_id'=> 7,
            ]);

            // 生成网易云IM帐户及专属客服群
            // 网易云ID
            $accid = UserProfile::makeImUser($user);

            // 创建网易云IM帐号
            $netease_res = call_netease('https://api.netease.im/nimserver/user/create.action', [
                'accid' => $accid,
                'name'  => $user->name,
                'icon'  => cdn(). $user_profile->avatar,
                // 'icon'  => 'https://mini.tosneaker.com'. $user_profile->avatar,
            ]);

            if ($netease_res['code'] == '200') {
                $user_profile->im_user  = $accid;
                $user_profile->im_token = $netease_res['info']['token'];
            }

            // 创建个人专属客服群
            $netease_res = call_netease('https://api.netease.im/nimserver/team/create.action', [
                'tname' => '个人专属客服',
                'owner' => $accid,
                'members'   => '[]',
                'msg'       => '个人专属客服群-邀您进入',  // 可能是建群时的邀请信息/也可能是一个邀请人时的信息
                'magree'    => 0,                           // 0-建群时不需要被邀请人同意加入群
                'joinmode'  => 2,                           // 2-不允许任何人加入
                'icon'      => '',                          // 群头像
                'beinvitemode'  => 1,                       // 1-建群后不需要被邀请人同意加入群
                'invitemode'    => 1,                       // 1-所有人都可以邀请他人入群
                'custom'        => json_encode(['type' => 'staff']),
            ]);

            if ($netease_res['code'] == '200') {
                $user_profile->im_staff_tid = $netease_res['tid'];
            }

            $user->profile()->save($user_profile);
            \Log::info($user_profile);

            // 事务提交
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $user;
    }

    /**
     * 制作一个新邀请码，在数据库里查下重
     * @return string                   新邀请码
     */
    public static function makeInvitationCode()
    {
        do {
            $invitation_code = str_rand(6);

            $exists = self::where('invitation_code', $invitation_code)->count();
        } while ($exists);

        return $invitation_code;
    }

    /**
     * 制作一个新二维码序列号
     * @return string                   新二维码序列号
     */
    public static function makeQrCode()
    {
        do {
            $qr_code = str_rand(12, true);

            $exists = self::where('qr_code', 'like binary', $qr_code)->count();
        } while ($exists);

        return $qr_code;
    }

    public static function  getUserIdByQrCode($qr_code)
    {
        $user = self::where('qr_code', 'like binary', $qr_code)->first();
        return $user ? $user->id : null;
    }

    /**
     * 判断用户的角色是否是某个值
     * @param  string   appraiser鉴定师  ,custom_design定制师
     * @return boolean     是否
     */
    public function roleIs($role = '')
    {
        if (empty($role)) {
            return false;
        }

        // 管理员和库管暂时用这个
        if ($role == 'admin' || $role == 'kuguan') {
            return (boolean) $this->roles()->where('slug', $role)->count();
        }

        // 鉴定师，定制师，特约作者（↓这个是关系）
        return (boolean) $this->$role()->count();
    }

    /**
     * 判断用户是否是客服
     * @param  string   $role           角色slug
     * @return boolean                  是否
     */
    public function isStaff()
    {
        // 商城的客服
        $mall_staffs  = MallModels\Setting::where('key', 'mall_staff')->first();

        $staff_ids  = explode(',', $mall_staffs->value);
        if (in_array($this->id, $staff_ids)) {
            return 'mall';
        }

        // 定制的客服
        $staff_ids  = CustomizationModels\CustomDesign::all()->pluck('user_id')->all();
        if (in_array($this->id, $staff_ids)) {
            return 'customization';
        }

        // 洗护的客服
        $care_staffs  = CareModels\Setting::where('key', 'care_staff')->first();

        $staff_ids  = explode(',', $care_staffs->value);
        if (in_array($this->id, $staff_ids)) {
            return 'care';
        }

        return false;
    }

//    // oauth 2.0
//    public function findForPassport($username)
//    {
//        return  $this->whereHas('profile', function ($query) use($username) {
//                    $query->where('mobile', $username);
//                })->first();
//    }

    // jwt
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public static function search($condition, $field = ['id', 'name'])
    {
        $q = self::select($field);

        if (isset($condition['wd'])) {
            $q->where('name', 'like', '%'.$condition['wd'].'%');
        }

        $take_num = self::LIMIT_PER_PAGE;
        if (isset($condition['limit'])) {
            $take_num = (int) $condition['limit'];
            $q->take($take_num);
        }

        if (isset($condition['page'])) {
            $skip_num = $take_num * ($condition['page'] - 1);
            $q->skip($skip_num)
              ->take($take_num);
        }

        return $q->get();
    }

    /**
     * 201805311509
     * Author: zhanglei
     * 通过 user表中 serial_number 获取用户的 user表中 id, 主要扫描二维码使用
     */
    public static function getUserBySerialNumber(string $serial_number)
    {
        if( !empty($serial_number) ){
            # 通过用户编号获取用户信息
            $user = self::where('serial_number', 'like binary', $serial_number)->first();
            return $user ? $user->id : null;
        }else{
            return null;
        }
    }

}
