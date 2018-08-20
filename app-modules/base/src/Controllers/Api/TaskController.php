<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Models;
use JWTAuth;
use Request;

class TaskController extends \BaseController
{
    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            $id = 0;
            if (Request::has('token')) {
                try {
                    JWTAuth::setToken(Request::input('token'));
                    $id = JWTAuth::toUser()->id;
                } catch (\Exception $e) {
                    // 什么都不做
                }
            }

            $task = Models\Task::select('id', 'name', 'description', 'max_coin')->orderBy('sort_order', 'asc')->get();

            if ($id) {
                $task->each(function ($item) use ($id) {
                    $today_earlist_time = date('Y-m-d');

                    switch ($item->id) {
                        case 1:
                            $checkin = Models\CheckIn::where('user_id', $id)->first();
                            $userProfile = Models\UserProfile::find($id);

                            if ($checkin) {
                                $today_earlist_timestamp = strtotime(date('Y-m-d'));
                                $last_checkin_timestamp = strtotime($checkin->updated_at);

                                //最后签到时间戳 >= 当天零点时间戳----》已签到
                                if ($last_checkin_timestamp >= $today_earlist_timestamp) {
                                    $is_checkin = true;
                                } else {
                                    //最后签到时间戳 < 当天零点时间戳----》未签到
                                    $is_checkin = false;

                                    //当天零点时间戳 - 最后签到时间戳 > 一天------》断签
                                    if ($today_earlist_timestamp - $last_checkin_timestamp > 24*3600) {
                                        $userProfile->continue_num = 0;
                                        $userProfile->save();
                                    }
                                }
                            } else {
                                $is_checkin = false;
                                $userProfile->continue_num = 0;
                                $userProfile->save();
                            }

                            $add_coin = (int) Models\UserCoinLog::where('user_id', $id)->where('get_way_id', 1)->where('created_at', '>=', $today_earlist_time)->sum('change_num');
                            $item->add_coin = $add_coin;
                            $item->continue_num = $userProfile->continue_num;
                            $item->is_checkin = $is_checkin;
                            break;
                        case 2:
                            $add_coin = (int) Models\UserCoinLog::where('user_id', $id)->where('get_way_id', 2)->sum('change_num');
                            $invite_num = Models\UserCoinLog::where('user_id', $id)->where('get_way_id', 2)->count();
                            $invitation_code = Models\User::find($id)->invitation_code;
                            $item->add_coin = $add_coin;
                            $item->invitation_code = $invitation_code;
                            $item->invite_num = $invite_num;
                            break;
                        case 3:
                            $add_coin = (int) Models\UserCoinLog::where('user_id', $id)->where('get_way_id', 3)->where('created_at', '>=', $today_earlist_time)->sum('change_num');
                            $item->add_coin = $add_coin;
                            break;
                        case 4:
                            $add_coin = (int) Models\UserCoinLog::where('user_id', $id)->where('get_way_id', 4)->where('created_at', '>=', $today_earlist_time)->sum('change_num');
                            $item->add_coin = $add_coin;
                            break;
                        case 5:
                            $add_coin = (int) Models\UserCoinLog::where('user_id', $id)->where('get_way_id', 5)->where('created_at', '>=', $today_earlist_time)->sum('change_num');
                            $item->add_coin = $add_coin;
                            break;
                    }
                });
            } else {
                $task->each(function ($item) {
                    $item->add_coin = $item->continue_num = $item->invite_num = 0;
                    $item->is_checkin = false;
                    $item->invitation_code = '';
                });
            }

            $res['data'] = $task;
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
