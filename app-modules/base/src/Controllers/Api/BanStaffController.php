<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Models;
use Modules\Mall\Models as MallModels;
use Modules\Customization\Models as CustomizationModels;
use Modules\Care\Models as CareModels;
use Request;
use Validator;
use Redis;
use JWTAuth;

class BanStaffController extends \BaseController
{
    const LIMIT_PER_PAGE = 10;

    public function index()
    {
        $res = parent::apiFetchedResponse();

        // 说明：
        // 1. 列出所有的客服，后台设置的，定制和洗护的服务商
        // 2. 然后把在线状态给他们赋上

        $validator = Validator::make(Request::all(), [
            'type'      => 'required|string|in:mall,customization,care',
            'online'    => 'digits:1',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $limit = (int) Request::input('limit') ?: self::LIMIT_PER_PAGE;
            $staff_master = Models\Setting::where('key', 'staff_master')->first();
            $staff_master = 7;

            // 取得各类型客服
            if (Request::input('type') == 'mall') {
                $mall_staffs  = MallModels\Setting::where('key', 'mall_staff')->first();

                if ($mall_staffs) {
                    $staff_ids = explode(',', $mall_staffs->value);
                } else {
                    $staff_ids = [];
                }

            } elseif (Request::input('type') == 'customization') {
                $staff_ids  = CustomizationModels\CustomDesign::all()->pluck('user_id')->all();

            } elseif (Request::input('type') == 'care') {
                $care_staffs  = CareModels\Setting::where('key', 'care_staff')->first();

                if ($care_staffs) {
                    $staff_ids = explode(',', $care_staffs->value);
                } else {
                    $staff_ids = [];
                }
            }

            if (Request::input('online')) {
                foreach ($staff_ids as $idx => $user_id) {
                    // 有key的就是在线,没key的unset掉,$staff_master不能unset掉
                    $login_ts = Redis::get('im:monitor_online:bu-'. sprintf('%010d', $user_id));
                    if (!$login_ts) {
                        unset($staff_ids[$idx]);
                    }
                }
            }

            $staff_ids  = array_merge([$staff_master], $staff_ids);
            $ids_ordered= implode(',', $staff_ids);

            $staff_list = Models\User::whereIn('id', $staff_ids)
                ->with('profile')
                ->orderByRaw(\DB::raw("FIELD(id, $ids_ordered)"))
                ->paginate($limit);

            // 查询客服在线状况
            // 然后合并进$staff_list
            // 如果选择了只列出在线的客服,需要在$staff_ids时就进行查找
            $staff_list->each(function($item) {
                if (Request::input('online')) {
                    $item->is_online = 1;
                } else {
                    $login_ts = Redis::get('im:monitor_online:'. $item->profile->im_user);
                    if (!$login_ts) {
                        $item->is_online = 0;
                    } else {
                        $item->is_online = 1;
                    }
                }

                $item->setVisible(['id', 'name', 'is_online', 'profile']);
                $item->profile->setVisible(['avatar', 'im_user']);
            });

            $res['data'] = $staff_list->items();

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function pull()
    {
        $res = parent::apiCreatedResponse();

        // 说明：
        // 1. 应该有batch在长时间无人聊天时，踢走客服
        // 2. 应该在拉客服时，改变客服在群里的昵称
        // 3. 只有在用户主动点击咨询时，才使用这个接口（例如:咨询某个商品，咨询某个定制i，主动咨询洗护）
        // 4. 在用户直接通过IM找客服时，只能由官方客服分配

        $validator = Validator::make(Request::all(), [
            'staff_user_id' => 'required|integer',
            'master'        => 'digits:1',
            'target_tid'    => 'integer',
        ]);

        $validator->sometimes(['target_tid'], 'required', function () {
            return Request::has('master');
        });

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // 找到客服
            $staff_user = Models\User::find(Request::input('staff_user_id'));
            if (!$staff_user || !$type = $staff_user->isStaff()) {
                abort(403, '客服不在');
                // TODO:如果客服不存在，可以只拉主管
            }

            // 找到客服主管
            $staff_master_id = Models\Setting::where('key', 'staff_master')->first();
            $staff_master_id = 7;    // fack
            $staff_master    = Models\User::find($staff_master_id);
            if (!$staff_master) {
                abort(403, '客服主管未设置');
            }

            $staff_accid    = $staff_user->profile->im_user;
            $master_accid   = $staff_master->profile->im_user;

            if (Request::has('master')) {
                if (JWTAuth::user()->id != $staff_master_id) {
                    abort(403, '您不是客服主管,不能拉人');
                }

                // TODO:判断主管是否已经是这个群的成员了(通过网易云)?
                // 不判断了，直接再拉一遍

                $target_tid = Request::input('target_tid');

                $user = Models\User::whereHas('profile', function($query) use($target_tid) {
                    $query->where('im_staff_tid', $target_tid);
                })->first();

                if (!$user) {
                    abort(403, '客服群不存在');
                }

                // 只拉客服
                $members = [$master_accid, $staff_accid];
            } else {
                $user       = JWTAuth::user();
                $target_tid = $user->profile->im_staff_tid;

                // 拉master和客服
                $members = [$master_accid, $staff_accid];
            }

            // 拉人入群的操作
            $netease_res = call_netease('https://api.netease.im/nimserver/team/add.action', [
                'tid'       => $target_tid,
                'owner'     => $user->profile->im_user,
                'members'   => json_encode($members),
                'msg'       => '个人专属客服群的邀您进入',
                'magree'    => 0,
            ]);

            if ($netease_res['code'] == '200') {
                $res['message'] = '进群成功';
            }

            // 更新IM群的自定义信息
            $this->_modifyGroupCustom($target_tid, $user);

            // 改昵称
            foreach ($members as $idx => $staff_accid) {
                // 主管不改名字（或者也改）
                if ($idx == 0) {
                    continue;
                }

                $this->_modifyGroupNick($target_tid, $user->profile->im_user, $staff_accid, $type);
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function join()
    {
        $res = parent::apiCreatedResponse();

        // 说明：
        // 1. 当时间过长,客服已被踢出群,还有事联系用户时.使用此接口强行入群
        // 2. 客服主管也会被拉入

        $validator = Validator::make(Request::all(), [
            'target_tid'     => 'integer',
            'target_user_id' => 'integer',
        ]);

        $validator->sometimes(['target_tid'], 'required', function () {
            return !Request::has('target_user_id');
        });

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            if (Request::input('target_tid')) {
                $target_tid = Request::input('target_tid');

                $user = Models\User::whereHas('profile', function($query) use($target_tid) {
                    $query->where('im_staff_tid', $target_tid);
                })->first();

                if (!$user) {
                    abort(403, '客要进入的客服群的主人已被删除');
                }
            } else {
                $user_id = Request::input('target_user_id');
                $user    = Models\User::find($user_id);

                if (!$user) {
                    abort(403, '要进入的客服群的主人已被删除');
                }

                $target_tid = $user->profile->im_staff_tid;
            }

            // 找到客服主管
            $staff_master_id = Models\Setting::where('key', 'staff_master')->first();
            $staff_master_id = 7;    // fack
            $staff_master    = Models\User::find($staff_master_id);
            if (!$staff_master) {
                abort(403, '客服主管未设置');
            }

            $staff_user = JWTAuth::user();
            if (!$type = $staff_user->isStaff()) {
                abort(403, '您不是客服,不能进群');
            }

            // 拉master和客服
            $members = [$staff_master->profile->im_user, $staff_user->profile->im_user];

            // 拉人入群的操作
            $netease_res = call_netease('https://api.netease.im/nimserver/team/add.action', [
                'tid'       => $target_tid,
                'owner'     => $user->profile->im_user,
                'members'   => json_encode($members),
                'msg'       => '个人专属客服群的邀您进入',
                'magree'    => 0,
            ]);

            if ($netease_res['code'] == '200') {
                $res['message'] = '进群成功';
                $res['data']['tid'] = $target_tid;
            }

            // 更新IM群的自定义信息
            $this->_modifyGroupCustom($target_tid, $user);

            // 改昵称
            foreach ($members as $idx => $staff_accid) {
                // 主管不改名字（或者也改）
                if ($idx == 0) {
                    continue;
                }

                $this->_modifyGroupNick($target_tid, $user->profile->im_user, $staff_accid, $type);
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    private function _modifyGroupCustom($tid, $owner)
    {
        // 更新IM群的自定义信息
        $netease_res = call_netease('https://api.netease.im/nimserver/team/update.action', [
            'tid'       => $tid,
            'owner'     => $owner->profile->im_user,
            'custom'    => json_encode([
                'type'          => 'staff',
                'owner_name'    => $owner->name,
                'owner_avatar'  => cdn() . $owner->profile->avatar,
            ]),
        ]);

        // XXX:这里custom写死成这样，以后别处加信息肯定会有坑
    }

    private function _modifyGroupNick($tid, $owner, $accid, $type = null)
    {
        $staff_prefixs = [
            'mall'          => '商城',
            'customization' => '定制',
            'care'          => '洗护',
        ];

        if (isset($staff_prefixs[$type])) {
            $prefix = $staff_prefixs[$type];
        } else {
            $prefix = '';
        }

        // 修改客服在IM群的昵称
        $netease_res = call_netease('https://api.netease.im/nimserver/team/updateTeamNick.action', [
            'tid'       => $tid,
            'owner'     => $owner,
            'accid'     => $accid,
            'nick'      => 'Ban'. $prefix .'客服',
        ]);
    }
}
