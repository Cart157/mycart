<?php

namespace Modules\Base\Controllers\Common\Netease;

use Modules\Base\Models;
use Request;
use Redis;

class ImController extends \BaseController
{
    public function notify()
    {
        \Log::info(Request::all());

        try {
            // 假设要监控的accid
            $staff_accid = 'bu-0000000007';
            // $staff_accid = 'bu-0000000176';

            if (Request::input('eventType') == 1 && Request::input('convType') == 'PERSON') {
                $from_accid = Request::input('fromAccount');
                $to_accid   = Request::input('to');

                if ($from_accid != $staff_accid && $to_accid != $staff_accid) {
                    abort(403, '不是客服咨询，不处理');
                }

                // 用户找客服聊天
                if ($to_accid == $staff_accid) {
                    $im_staff_key = "im:monitor_staff:{$from_accid}";

                    // 如果存在就删除（说明已经看了，如果有已读抄送就更好了）
                    if (Redis::hlen($im_staff_key)) {
                        Redis::del($im_staff_key);
                    }

                    // 新建一个（加q就是question的意思，其实是为了排序方便，排在staff前面）
                    Redis::hset($im_staff_key, 'quser_'. Request::input('msgTimestamp'), json_encode(Request::all(), JSON_UNESCAPED_UNICODE));

                    // 7天后过期
                    Redis::expire($im_staff_key, 3600 * 24 * 7);

                // 客服找用户聊天
                } else {
                    $im_staff_key = "im:monitor_staff:{$to_accid}";

                    // 允许客服主动发起聊天
                    if (!Redis::hlen($im_staff_key)) {
                        // abort(403, '用户未咨询客服主动发起聊天，不处理');
                        Redis::hset($im_staff_key, 'quser_'. Request::input('msgTimestamp'), json_encode([
                            'fromAccount' => $to_accid,
                            'body'        => '洗护咨询',
                        ], JSON_UNESCAPED_UNICODE));
                    }

                    Redis::hset($im_staff_key, 'staff_'. Request::input('msgTimestamp'), json_encode(Request::all(), JSON_UNESCAPED_UNICODE));

                    // 判断聊天信息的关键词，然后加个 'type' 进去
                    if (!Redis::hexists($im_staff_key, 'type')) {
                        Redis::hset($im_staff_key, 'type', 'care');
                    }
                }
            }

            // // 群聊才处理（而且是客服群）
            // if (Request::input('eventType') == 1 && Request::input('convType') == 'TEAM') {
            //     $tid = Request::input('to');

            //     $user_profile = Models\UserProfile::where('im_staff_tid', $tid)->first();

            //     if (!$user_profile) {
            //         abort(403, '不是客服群，不处理');
            //     }

            //     $im_staff_key = "im:monitor_staff:{$tid}";

            //     // 看说话的人是否是本群的主人
            //     // 如果是群主说话，说明已阅，清除原来的hash，创建一个新的
            //     // TODO:还要限制在小程序（只有用户限制在小程序，客服不限制）
            //     if (Request::input('fromAccount') == $user_profile->im_user) {
            //         // 如果存在就删除（说明已经看了，如果有已读抄送就更好了）
            //         if (Redis::hlen($im_staff_key)) {
            //             Redis::del($im_staff_key);
            //         }

            //         // 新建一个（加q就是question的意思，其实是为了排序方便，排在staff前面）
            //         Redis::hset($im_staff_key, 'quser_'. Request::input('msgTimestamp'), json_encode(Request::all(), JSON_UNESCAPED_UNICODE));

            //         // 7天后过期
            //         Redis::expire($im_staff_key, 3600 * 24 * 7);

            //         // TODO:用户主动发起聊天时，应该把客服主管拉进群
            //         // 找到客服主管
            //         $staff_master_id = Models\Setting::where('key', 'staff_master')->first();
            //         $staff_master_id = 7;    // fack
            //         $staff_master    = Models\User::find($staff_master_id);
            //         if (!$staff_master) {
            //             abort(403, '客服主管未设置');
            //         }

            //         $netease_res = call_netease('https://api.netease.im/nimserver/team/add.action', [
            //             'tid'       => $tid,
            //             'owner'     => $user_profile->im_user,
            //             'members'   => json_encode([$staff_master->profile->im_user]),
            //             'msg'       => '个人专属客服群的邀您进入',
            //             'magree'    => 0,
            //         ]);

            //     // 如果不是群主说话（即为客服），如果存在对这个群的监控，向里添加消息
            //     } elseif (Redis::hlen($im_staff_key)) {
            //         Redis::hset($im_staff_key, 'staff_'. Request::input('msgTimestamp'), json_encode(Request::all(), JSON_UNESCAPED_UNICODE));

            //         // 判断聊天信息的关键词，然后加个 'type' 进去
            //         if (!Redis::hexists($im_staff_key, 'type')) {
            //             $msg_content = Request::input('fromNick') .':'. Request::input('body');

            //             $type_keywords = [
            //                 'mall'          => '商城',
            //                 'customization' => '定制',
            //                 'care'          => '洗护',
            //             ];

            //             foreach ($type_keywords as $type => $keyword) {
            //                 // 如果找到关键词，就把类型保存进来，结束循环
            //                 // 给小程序发通知时，根据这个来判断发往那个小程序
            //                 if (str_contains($msg_content, $keyword)) {
            //                     Redis::hset($im_staff_key, 'type', $type);
            //                     break;
            //                 }
            //             }
            //         }
            //     } else {
            //         // 都不满足，记log
            //         \Log::error('客服群消息，用户非小程序咨询，客服主动发起聊天');
            //     }
            // }

            // IM登入时记录登入记录
            if (Request::input('eventType') == 2) {
                $im_user = Request::input('accid');
                $im_online_key = "im:monitor_online:{$im_user}";

                Redis::set($im_online_key, Request::input('timestamp'));

                // im很难24小时在线，如果有应该是出错了，所以设置一个过期时间
                Redis::expire($im_online_key, 3600 * 24);
            }

            // IM登出时清除登入记录
            if (Request::input('eventType') == 3) {
                $im_user = Request::input('accid');
                $im_online_key = "im:monitor_online:{$im_user}";

                Redis::del($im_online_key);
            }

        } catch (\Exception $e) {
            // 什么也不做
            //
            // [2018-07-13 15:17:49] local.INFO: array (
            //   'fromNick' => '橡胶.D.霸气',
            //   'msgType' => 'TEXT',
            //   'msgidServer' => '22741236376929060',
            //   'fromAccount' => 'bu-0000100011',
            //   'fromClientType' => 'AOS',
            //   'fromDeviceId' => 'c8b78266-3d31-4f92-8752-153bec9d9857',
            //   'tMembers' => '[bu-0000103159, bu-0000100441, bu-0000101212, bu-0000102089, bu-0000102973, bu-0000104073, bu-0000102313, bu-0000102112, bu-0000100717, bu-0000100358, bu-0000100006, bu-0000103114, bu-0000103083, bu-0000105910, bu-0000102088, bu-0000102298]',
            //   'eventType' => '1',
            //   'body' => '测试',
            //   'convType' => 'TEAM',
            //   'msgidClient' => '1c4ee91b0c304d57aadf79d574058a66',
            //   'resendFlag' => '0',
            //   'msgTimestamp' => '1531466268688',
            //   'to' => '338870829',
            // )
            //
  // 'fromNick' => '橡胶.D.霸气',
  // 'msgType' => 'TEXT',
  // 'msgidServer' => '36004057726',
  // 'fromAccount' => 'bu-0000100011',
  // 'fromClientType' => 'WEB',
  // 'fromDeviceId' => '170a958f23ac7156ed7744ce91bc674d',
  // 'eventType' => '1',
  // 'body' => '从',
  // 'convType' => 'PERSON',
  // 'msgidClient' => 'ec3905c37f38b8dc28acee86a0f2ecb2',
  // 'resendFlag' => '0',
  // 'msgTimestamp' => '1533236569550',
  // 'to' => 'bu-0000000176',
            //
            // 登入登出
            // [2018-07-23 00:00:06] local.INFO: array (
            //   'code' => '200',
            //   'clientType' => 'AOS',
            //   'clientIp' => '117.10.32.193',
            //   'accid' => 'bu-0000103633',
            //   'sdkVersion' => '39',
            //   'eventType' => '2',
            //   'timestamp' => '1532275205183',
            // )
        }

        return 'success';
    }
}
