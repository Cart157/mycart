<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Models;
use Carbon\Carbon;
use Request;
use Validator;
use JWTAuth;

class CheckInController extends \BaseController
{
    public function show($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            $user = Models\User::select('id', 'name')->findOrFail($id);

            if ($id != JWTAuth::user()->id) {
                abort(403, '没有权限，无权访问！');
            }

            $checkin = Models\CheckIn::where('user_id', $id)->first();

            if ($checkin) {
                $today_earlist_time = strtotime(date('Y-m-d'));
                $last_checkin_time = strtotime($checkin->updated_at);

                //最后签到时间戳 >= 当天零点时间戳----》已签到
                if ($last_checkin_time >= $today_earlist_time) {
                    $is_checkin = true;
                } else {
                    //最后签到时间戳 < 当天零点时间戳----》未签到
                    $is_checkin = false;

                    //当天零点时间戳 - 最后签到时间戳 > 一天------》断签
                    if ($today_earlist_time - $last_checkin_time > 24*3600) {
                        $userProfile = Models\UserProfile::find($id);
                        $userProfile->continue_num = 0;
                        $userProfile->save();
                    }
                }
            } else {
                $is_checkin = false;
                $userProfile = Models\UserProfile::find($id);
                $userProfile->continue_num = 0;
                $userProfile->save();
            }

            $res['data'] = $user;
            $res['data']->avatar = $user->profile->avatar;
            $res['data']->coin_num = $user->profile->coin_num;
            $res['data']->continue_num = $user->profile->continue_num;
/*
            $max_continue_num = $user->profile->continue_num >= 10 ? 10 : $user->profile->continue_num;
            $res['data']->add_coin_num = 5 * $max_continue_num;
 */
            $max_continue_num = $user->profile->continue_num >= 7 ? 7 : $user->profile->continue_num;
            $res['data']->add_coin_num = $max_continue_num ? $max_continue_num * 5 + 15 : 0;

            $res['data']->is_checkin = $is_checkin;
            unset($res['data']->profile);
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function store($id)
    {
        $res = parent::apiCreatedResponse();

        try {
            // 1、验证用户是否存在
            $user = Models\User::select('id', 'name')->findOrFail($id);

            // 2、验证是否本人操作
            if ($id != JWTAuth::user()->id) {
                abort(403, '没有权限，无权访问！');
            }

            $checkin = Models\CheckIn::where('user_id', $id)->first();

            // 3、判断该用户是否有签到记录
            if ($checkin) {
                $today_earlist_time = strtotime(date('Y-m-d'));
                $last_checkin_time = strtotime($checkin->updated_at);

                // 4、判断该用户是否重复签到
                if ($last_checkin_time >= $today_earlist_time) {
                    abort(403, '您已经签到过了');
                } else {
                    // 5、判断该用户是否断签，断签则设连续签到天数为0
                    if ($today_earlist_time - $last_checkin_time > 24*3600) {
                        $profile_before = Models\UserProfile::find($id);
                        $profile_before->continue_num = 0;
                        $profile_before->save();
                    }
                }
            } else {
                $profile_before = Models\UserProfile::find($id);
                $profile_before->continue_num = 0;
                $profile_before->save();
            }

            // 6、没有签到记录、没有重复签到可以签到
            $checkedin = Models\CheckIn::updateOrCreate(
                ['user_id' => $id],
                ['updated_at' => new Carbon()]
            );
            $checkedin->save();

            // 7、签到后修改连签天数、积分
            $profile_after = Models\UserProfile::find($id);
            $profile_after->continue_num = $profile_after->continue_num + 1;
/*
            $max_continue_num = $profile_after->continue_num >= 10 ? 10 : $profile_after->continue_num;
            $add_coin_num = $max_continue_num * 5;
 */
            $max_continue_num = $profile_after->continue_num >= 7 ? 7 : $profile_after->continue_num;
            $add_coin_num = $max_continue_num ? $max_continue_num * 5 + 15 : 0;

            $profile_after->save();

            // 打log
            finish_task_add_coin($id, 1, $add_coin_num);

            $res['message'] = '签到成功！';
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

}