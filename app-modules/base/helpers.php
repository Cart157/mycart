<?php

/**
 * 通过切换命名空间，换主题
 * @param  string $view 模板的key
 * @return string       返回新的key
 */
function tpl($view = null)
{
    $theme = 'default';
    $tpl = $theme . '::' . $view;

    $factory = app('Illuminate\Contracts\View\Factory');
    if (!$factory->exists($tpl)) {
        $tpl = 'default::' . $view;
    }

    return $tpl;
}

/**
 * 切换静态文件的cdn，默认不实用cdn
 * @param  string $str 七牛的二级域名
 * @return string      服务器域名
 */
function cdn()
{
    return config('qiniu.cdn');
}

/**
 * css & js是否使用cdn
 */
function asset_cdn()
{
    return env('ASSET_CDN', true) ? config('qiniu.cdn') : '';
}

/**
 * 后端是否使用cdn
 */
function admin_cdn()
{
    return env('ADMIN_CDN', true) ? config('qiniu.cdn') : '';
}

/**
 * 生成css文件的引用，为了方便切换cdn
 * @param  string $asset css文件的路径
 * @return string        css的引用html
 */
function style($asset)
{
    $asset = asset_cdn() . $asset;
    return sprintf('<link href="%s" rel="stylesheet">', $asset);
}

/**
 * 生成js文件的引用，为了方便切换cdn
 * @param  string $asset js文件的路径
 * @return string        js的引用html
 */
function script($asset)
{
    $asset = asset_cdn() . $asset;
    return sprintf('<script src="%s"></script>', $asset);
}

function current_module()
{
    return isset(Route::current()->action['module']) ? Route::current()->action['module'] : 'base';
}

function get_qiniu_key($url)
{
    $path = str_replace(config('qiniu.cdn'), '', $url);

    return starts_with($path, '/') ? substr($path, 1) : $path;
}

function move_qiniu_uploads($source, $target)
{
    $accessKey  = config('qiniu.access_key');
    $secretKey  = config('qiniu.secret_key');
    $bucket     = config('qiniu.bucket');

    $auth = new \Qiniu\Auth($accessKey, $secretKey);
    $config = new \Qiniu\Config();
    $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

    $srcBucket = $bucket;
    $tgtBucket = $bucket;

    $target = substr($target, 1);
    $err = $bucketManager->move($srcBucket, $source, $tgtBucket, $target, true);
    if ($err) {
        throw new \Exception($err->getResponse()->error);
    }
}

function del_qiniu($key)
{
    $accessKey  = config('qiniu.access_key');
    $secretKey  = config('qiniu.secret_key');
    $bucket     = config('qiniu.bucket');
    $auth = new \Qiniu\Auth($accessKey, $secretKey);
    $config = new \Qiniu\Config();
    $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

    $err = $bucketManager->delete($bucket, $key);
    if ($err) {
        throw new \Exception($err->getResponse()->error);
    }
}

function call_netease($api, $data)
{
    $nonce      = mt_rand(100000, 999999);
    $cur_time   = time();
    $check_sum  = sha1('2144fd0f6416' . $nonce . $cur_time);

    $guzzle = new \GuzzleHttp\Client(['verify' => false]);
    $response = $guzzle->post($api, [
        'body'    => http_build_query($data),
        'headers' => [
            'Content-Type'  => 'application/x-www-form-urlencoded;charset=utf-8',
            'AppKey'        => 'd988edda82c87e01723014b7df8b031b',
            'Nonce'         => $nonce,
            'CurTime'       => $cur_time,
            'CheckSum'      => $check_sum,
        ],
    ]);

    $netease_res = json_decode((string) $response->getBody(), true);

    return $netease_res;
}

function call_xinge($content, $account, $data)
{
    require_once ('libraries/XingeApp.php');

    $push = new XingeApp(2200276558, '95ed3369dda9b95a75700752e83b72a6');
    $mess = new MessageIOS();

    $mess->setExpireTime(86400);
    $mess->setAlert($content);
    $mess->setSound("beep.wav");
    $custom = $data;
    $mess->setCustom($custom);
    $accept_time = new TimeInterval(0, 0, 23, 59);
    $mess->addAcceptTime($accept_time);

    $xinge_res = $push->PushSingleAccount(0, $account, $mess, XingeApp::IOSENV_DEV);

    if ($xinge_res['ret_code'] == 0) {
        $xinge_res['device_type'] = 'ios';
        return $xinge_res;
    }

    $push = new XingeApp(2100275723, '19e93f2f548bcbf43783f32b5738a723');
    $mess = new Message();

    $mess->setExpireTime(86400);
    $mess->setContent($content);
    $mess->setType(Message::TYPE_NOTIFICATION);

    $style = new Style(0);
    $style = new Style(0,1,1,0);
    $action = new ClickAction();
    $action->setActionType(ClickAction::TYPE_ACTIVITY);
    $action->setActivity(config('const.android_activity')[$data['type']]);
    $custom = $data;
    $mess->setStyle($style);
    $mess->setAction($action);
    $mess->setCustom($custom);

    // $xinge_res = $push->PushSingleDevice('token', $mess);
    $xinge_res = $push->PushSingleAccount(0, $account, $mess);

    $xinge_res['device_type'] = 'android';
    return $xinge_res;
}

function wx_miniapp_decrypt($appid, $sessionKey, $encryptedData, $iv)
{
    include_once "libraries/wx_minapp/wxBizDataCrypt.php";

    $pc = new WXBizDataCrypt($appid, $sessionKey);
    $errCode = $pc->decryptData($encryptedData, $iv, $data);

    if ($errCode == 0) {
        return $data;
    } else {
        abort($errCode, '解密失败');
    }
}

function qiniu_refresh_urls(...$url_arr)
{
    $ak   = 'PRQ6YEFwh3jWh-2a2IduBRxpOX3QVXRoCFFAH7XT';
    $sk   = '9wntfgInHSoAaB3hlUH3DILWQXifcu8UNBtrBjkM';
    $auth = new Qiniu\Auth($ak, $sk);

    $bucketMgr = new Qiniu\Storage\BucketManager($auth);
    foreach ($url_arr as $url) {
        $bucketMgr->delete('tosneaker-com', get_qiniu_key($url));
    }

    $access_token = $auth->signRequest('/v2/tune/refresh', '');

    $data = [
        'urls' => $url_arr,
    ];

    $guzzle = new \GuzzleHttp\Client();
    $response = $guzzle->post('http://fusion.qiniuapi.com/v2/tune/refresh', [
        'body'    => json_encode($data),
        'headers' => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'QBox '. $access_token,
        ],
    ]);

    $response = json_decode((string) $response->getBody(), true);

    return $response;
}
//阿里云图片标签
function ali_image_tag($image_url)
{
    //"http://p3eglbig1.bkt.clouddn.com/uploads/_tmp/001a792f-3a81-4812-b2f4-783fe775d478.png"
    $guzzle = new GuzzleHttp\Client;
    $appcode = config('aliyun.ImageTagAppCode');
    $response = $guzzle->post('http://txcjsb.market.alicloudapi.com/image/scene', [
        'body' => '{
            "type": 0,
            "image_url": "'.$image_url.'"
        }',
        'headers' => [
            "Authorization"=> 'APPCODE '. $appcode,
            'Content-Type' => 'application/json;charset=utf-8',

        ],
    ]);

    return json_decode((string) $response->getBody(), true);
}
//七牛相似图片
function qiniu_resemble_image($token,$method,$url,$body = null)
{
    $guzzle = new GuzzleHttp\Client;
    if (strtoupper($method) == 'POST') {
        $response = $guzzle->post($url, [
            'body' => $body,
            'headers' => [
                "Authorization"=> $token,
                'Content-Type' => 'application/json',

            ],
        ]);
    }else if (strtoupper($method) == 'GET') {
        $response = $guzzle->get($url, [
            'headers' => [
                "Authorization"=> $token
            ],
        ]);
    }
    return json_decode((string) $response->getBody(), true);
}

function location($code = null, $mode = 'name')
{
    $locations = require('config/location.php');

    $area_info = null;

    // 无参，错参时直接返回省级列表
    if (is_null($code) || !is_numeric($code) || strlen($code) != 6) {
        $area_info = $locations['province_list'];
    } if (ends_with($code, '0000')) {
        $type = 'province';
    } elseif (ends_with($code, '00')) {
        $type = 'city';
    } else {
        $type = 'district';
    }

    if ($type == 'province') {
        if (($mode == 'name' || $mode == 'detail')
          && !is_null($code) && isset($locations['province_list'][$code])) {
            $area_info = $locations['province_list'][$code];
        } elseif (isset($locations['city_list'][$code])) {
            $area_info = $locations['city_list'][$code];
        } else {
            $area_info = [$code => location($code)];
        }
    } elseif ($type == 'city') {
        // 取得城市名，或详细
        if ($mode == 'name' || $mode == 'detail') {
            $parentCode = substr($code, 0, 2) . '0000';
            if (isset($locations['city_list'][$parentCode][$code])) {
                $area_info = $locations['city_list'][$parentCode][$code];
            }

            // 两个特殊的还是用上级省名
            if ($area_info == '市辖区' || $area_info == '县') {
                $area_info = location($parentCode);

            // 上面两种detail也是上级省名，所以没有detail
            } elseif ($mode == 'detail') {
                $area_info = location($parentCode).$area_info;
            }

        // 取得城市里的区县
        } elseif (isset($locations['district_list'][$code])) {
            $area_info = $locations['district_list'][$code];
        } else {
            $area_info = [$code => location($code)];
        }
    } elseif ($type == 'district') {
        if ($mode == 'name' || $mode == 'detail') {
            $parentCode = substr($code, 0, 4) . '00';
            if (isset($locations['district_list'][$parentCode][$code])) {
                $area_info = $locations['district_list'][$parentCode][$code];
            }

            if ($mode == 'detail') {
                $area_info = location($parentCode, 'detail').$area_info;
            }
        }
    }

    return $area_info;
}

// function location($code = null, $list = false)
// {
//     $locations = require('config/location.php');

//     $area_info = null;

//     if (is_null($code) || !is_numeric($code) || strlen($code) != 6) {
//         $area_info = $locations['province_list'];
//     } if (ends_with($code, '0000')) {
//         $type = 'province';
//     } elseif (ends_with($code, '00')) {
//         $type = 'city';
//     } else {
//         $type = 'district';
//     }

//     if ($type == 'province') {
//         if (!$list && !is_null($code) && isset($locations['province_list'][$code])) {
//             $area_info = $locations['province_list'][$code];
//         } elseif (isset($locations['city_list'][$code])) {
//             $area_info = $locations['city_list'][$code];
//         }
//     } elseif ($type == 'city') {
//         if (!$list) {
//             $parentCode = substr($code, 0, 2) . '0000';
//             if (isset($locations['city_list'][$parentCode][$code])) {
//                 $area_info = $locations['city_list'][$parentCode][$code];
//             }

//             if ($area_info == '市辖区') {
//                 $area_info = location($parentCode);
//             } elseif ($area_info == '县') {
//                 $area_info = location($parentCode).'下级县';
//             }
//         } elseif (isset($locations['district_list'][$code])) {
//             $area_info = $locations['district_list'][$code];
//         }
//     } elseif ($type == 'district') {
//         if (!$list) {
//             $parentCode = substr($code, 0, 4) . '00';
//             if (isset($locations['district_list'][$parentCode][$code])) {
//                 $area_info = $locations['district_list'][$parentCode][$code];
//             }

//             if ($area_info == '市辖区') {
//                 $area_info = location($parentCode);
//             }
//         }
//     }

//     return $area_info;
// }

function str_rand($len = 16, $upper_case = false)
{
    $str='0123456789abcdefghijkmnpqrstuvwxyzABCDEFGHIGKLMNPQRSTUVWXYZ';

    if ($upper_case) {
        $max_pos = strlen($str) - 1;
    } else {
        $max_pos = strlen($str) - 27;
    }

    $rand_str = '';
    for($i=0; $i < $len; $i++){
        $rand_str .= $str[mt_rand(0, $max_pos)];
    }

    return $rand_str;
}

/**
 * [service_notify description]
 * @param  Modules\Base\Models\User                     $to_user    [description]
 * @param  Modules\Base\Models\ServiceNotificationTpl   $tpl        [description]
 * @param  array                                        $message    [description]
 * @param  mixed(null|string)                           $ma_type    [description]
 * @return void                                                     [description]
 */
function service_notify($to_user, $tpl, $message)
{
    // 说明：小程序的变量名用 keyword1,2,3表示
    // 而公众号的变量名是有意义的，公众号转小程序好转，反之没办法
    // 所以app的和公众号保持一致
    $ma_message = $message; $idx = 1;

    foreach ($message['data'] as $arg => $val) {
        if (!is_array($val)) {
            $message['data'][$arg] = ['value' => strval($val)];
        } elseif (isset($val['value'])) {
            continue;
        } elseif (count($val) >= 2) {
            $message['data'][$arg] = [
                'value' => $val[0],
                'color' => $val[1],
            ];
        }
    }

    // 生成给app用的服务通知
    Modules\Base\Models\ServiceNotification::create([
        'user_id'       => $to_user->id,
        'tpl_id'        => $tpl->id,
        'template'      => $tpl->template,
        'message_info'  => json_encode($message, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
    ]);

    // if ($ma_type && $tpl->wx_template_id) {
    //     wx_miniapp_service_notify($to_user, $tpl->wx_template_id, $message, $ma_type)
    // }

    // if ($ma_type && $tpl->ali_template_id) {
    //     wx_miniapp_service_notify($to_user, $tpl->ali_template_id, $message, $ma_type)
    // }
}

function wx_miniapp_service_notify($to_user, $template_id, $message, $ma_type = null)
{
    // 验证$ma_type是否存在
    if (!$ma_type) {
        return;
    }

    if (is_object($to_user)) {
        $to_user = $to_user->id;
    }

    // 说明：小程序的变量名用 keyword1,2,3表示
    // 而公众号的变量名是有意义的，公众号转小程序好转，反之没办法
    // 所以app的$message和公众号保持一致
    $ma_message = $message; $idx = 1;

    foreach ($message['data'] as $arg => $val) {
        if (!is_array($val)) {
            $message['data'][$arg] = ['value' => strval($val)];
        } elseif (isset($val['value'])) {
            continue;
        } elseif (count($val) >= 2) {
            $message['data'][$arg] = [
                'value' => $val[0],
                'color' => $val[1],
            ];
        }

        $ma_message['data']['keyword'. $idx] = $message['data'][$arg];
        $idx++;
    }


    // TODO:从模板里取出对应的小程序模板或公众号模板，同时发出
    // 小程序的不方便存数据库，只有微信还好说，一个有阿里了呢，模板个格式不好统一

    // 取得对应的openid
    $wechat_user = Modules\Wechat\Models\Openid::where('user_id', $to_user)->first();

    $openid_field= "ma_{$ma_type}_openid";
    if ($wechat_user && $wechat_user->{$openid_field}) {
        $ma_openid = $wechat_user->{$openid_field};

        $formid = wx_miniapp_formid($ma_type, $ma_openid);

        if ($formid) {
            $app = EasyWeChat::miniProgram($ma_type);

            $app->template_message->send([
                'touser'            => $ma_openid,
                'template_id'       => $template_id,
                'page'              => $ma_message['page'],
                'form_id'           => $formid,
                'data'              => $ma_message['data'],
                'emphasis_keyword'  => $ma_message['emphasis_keyword'] ?? null,
            ]);

            // 删除使用过的$formid
            Redis::zrem("wechat:ma_{$ma_type}:formid:{$ma_openid}", $formid);
        }
    }
}

function wx_miniapp_formid($type, $openid)
{
    $total = Redis::zcard("wechat:ma_{$type}:formid:{$openid}");

    if (!$total) {
        return null;
    }

    // 这里移除过期的formid
    // ZREMRANGEBYSCORE
    $max = time() - 3600 * 24 * 7 + 5; // +5为了本次使用
    Redis::zremrangebyscore("wechat:ma_{$type}:formid:{$openid}", 0, $max);

    // 取得时间最靠前的一个
    $ret_array = Redis::zrange("wechat:ma_{$type}:formid:{$openid}", 0, 1);

    return $ret_array[0];
}

function system_notify($to_user, $tpl, $message)
{
    Modules\Base\Models\NotificationService::create([
        'user_id'       => $to_user->id,
        'tpl_id'        => $tpl->id,
        'message_info'       => json_encode($message, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
    ]);
}

function system_notice($to, $message, $from_user = null)
{
    if (!isset($message['type']) || !isset($message['message'])) {
        Log::error("消息体格式不正确，缺少 type/message/attach 等数据");
        return;
    }

    if (!is_object($to)) {
        $to_user = Modules\Base\Models\User::find($to);
        if (!$to_user) {
            Log::error("系统消息发送失败：用户不存在，user_id({$to})");
            return;
        }
    } else {
        if ($to instanceof Modules\Base\Models\User) {
            $to_user = $to;
        } else {
            Log::error('系统消息发送失败：用户对象类型不正确');
            return;
        }
    }

    $job = new App\Jobs\SystemNoticeToUser($to_user, $message, $from_user);
    dispatch($job);
    // if (!isset($message['type']) || !isset($message['message'])) {
    //     abort(500);
    // }

    // $attach = $message;

    // // 先存数据库
    // Modules\Base\Models\SystemNotification::create([
    //     'user_id'       => $to,
    //     'from_user_id'  => $from_user->id,
    //     'type'          => array_pull($message, 'type'),
    //     'message'       => array_pull($message, 'message'),
    //     'extra'         => empty($message) ? null : json_encode($message, JSON_UNESCAPED_UNICODE),
    // ]);

    // $guzzle = new GuzzleHttp\Client();

    // $from       = 'bu-'. sprintf('%010s', 175);
    // $to         = 'bu-'. sprintf('%010s', $to);
    // $nonce      = mt_rand(100000, 999999);
    // $cur_time   = time();
    // $check_sum  = sha1('2144fd0f6416' . $nonce . $cur_time);

    // $notice_info = [
    //     'msgtype'   => 0,
    //     'from'      => $from,
    //     'to'        => $to,
    //     'attach'    => $from_user->name . $attach['message'],
    // ];

    // $response = $guzzle->post('https://api.netease.im/nimserver/msg/sendAttachMsg.action', [
    //     'body'    => http_build_query($notice_info),
    //     'headers' => [
    //         'Content-Type'  => 'application/x-www-form-urlencoded;charset=utf-8',
    //         'AppKey'        => 'd988edda82c87e01723014b7df8b031b',
    //         'Nonce'         => $nonce,
    //         'CurTime'       => $cur_time,
    //         'CheckSum'      => $check_sum,
    //     ],
    // ]);

    // $netease_res = json_decode((string) $response->getBody(), true);

    // return $netease_res;
}

function system_notice_batch($to_users, $message, $from_user = null)
{
    if (!isset($message['type']) || !isset($message['message']) || !isset($message['attach'])) {
        Log::error("消息体格式不正确，缺少 type/message/attach 等数据");
        return;
    }

    if (!is_array($to_users)) {
        if ($to_users instanceof Illuminate\Database\Eloquent\Collection) {
            $to_users = $to_users->all();
        } else {
            Log::error('系统消息发送失败：用户对象类型不正确');
            return;
        }
    }

    $job = new App\Jobs\SystemNoticeToManyUsers($to_users, $message, $from_user);
    dispatch($job);
}

/**
 * 点赞加金币，打金币变化log
 * @param  $user_id         用户ID
 * @param  $task_id         任务ID
 * @return $add_coin_num    增加金币数量
 */
function finish_task_add_coin($user_id, $task_id, $add_coin_num)
{
    $today_earlist_time = date('Y-m-d');
    // 当天增加的金币
    $add_coin = (int) Modules\Base\Models\UserCoinLog::where('user_id', $user_id)->where('get_way_id', $task_id)->where('created_at', '>=', $today_earlist_time)->sum('change_num');
    // 该任务每日最大可得金币数
    $max_coin = Modules\Base\Models\Task::find($task_id)->max_coin;
    if ($add_coin < $max_coin) {
        if ($max_coin - $add_coin < $add_coin_num) {
            $add_coin_num = $max_coin - $add_coin;
        }
        $userProfile = Modules\Base\Models\UserProfile::find($user_id);
        $userProfile->coin_num = $userProfile->coin_num + $add_coin_num;
        $userProfile->save();

        Modules\Base\Models\UserCoinLog::create([
            'user_id'   => $user_id,
            'change_num'=> $add_coin_num,
            'get_way_id'=> $task_id
        ]);
    }
}

/**
 * 把明确时间转化成智能时间（例如：3秒前，5天前）
 * @param  string $str 代表时间的字符串
 * @return string      智能时间（例如：3秒前，5天前）
 */
function smart_time($str)
{
    $ts = strtotime($str);
    $passed = time() - $ts;

    if ($passed < 60) {
        return $passed . '秒前';
    } elseif ($passed < 3600) {
        $minute = floor($passed / 60);
        return $minute . '分钟前';
    } elseif ($passed < 3600 * 24) {
        $hour = floor($passed / 3600);
        return $hour . '小时前';
    } else {
        $day = floor($passed / (3600 * 24));
        if ($day < 31) {
            return $day . '天前';
        }
    }

    return date('Y-m-d', $ts);
}

function format_date($date)
{
    $current = time();
    $format = strtotime($date);
    if ($current >= $format) {
        $diff = $current - $format;
    } else {
        return '未来';
    }

    $array = [
        '31536000'  =>'年',
        '2592000'   =>'个月',
        '604800'    =>'星期',
        '86400'     =>'天',
        '3600'      =>'小时',
        '60'        =>'分钟',
        '1'         =>'秒'
    ];

    foreach ($array as $key => $value) {
        $floor = (int) floor($diff / (int)$key);
        if (0 != $floor) {
            return $floor.$value.'前';
        }
    }
}

function distance_datetime($date)
{
    $current = time();
    $format = strtotime($date);
    if ($format >= $current) {
        $diff = $format - $current;
    } else {
        return $date;
        return '以前';
    }

    $array = [
        '31536000'  =>'年',
        '2592000'   =>'个月',
        '604800'    =>'星期',
        '86400'     =>'天',
        '3600'      =>'小时',
        '60'        =>'分钟',
        '1'         =>'秒'
    ];

    foreach ($array as $key => $value) {
        $floor = (int) floor($diff / (int)$key);
        if (0 != $floor) {
            return $floor.$value;
        }
    }
}

/**
 * 取得访客的ip地址
 * @return [type] [description]
 */
function get_client_ip()
{
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif (!empty($_SERVER["REMOTE_ADDR"])) {
        $ip = $_SERVER["REMOTE_ADDR"];
    } else {
        $ip = "unknown";
    }

    return $ip;
}

/**
 * 生成网页的seo标题
 * @param  mixed  $feed      字符串或数组，组成标题的各级名称
 * @param  string $separator 标题分隔符
 * @return string            网页的标题
 */
function seo_title($feed, $separator = ' - ')
{
    if (!is_array($feed)) {
        $feed = [$feed];
    }

    $title = implode($separator, $feed);

    return $title;
}

/**
 * 生成网页的seo描述
 * @param  string $html 从富文本中截取50文字
 * @return string       网页的标题
 */
function seo_desc($html, $length = 50)
{
    $search = [
        "'<script[^>]*?>.*?</script>'si", // 去掉 javascript
        "'<[\/\!]*?[^<>]*?>'si", // 去掉 HTML 标记
        "'([\r\n])[\s]+'", // 去掉空白字符
        "'&(quot|#34);'i", // 替换 HTML 实体
        "'&(amp|#38);'i",
        "'&(lt|#60);'i",
        "'&(gt|#62);'i",
        "'&(nbsp|#160);'i",
        "'&(iexcl|#161);'i",
        "'&(cent|#162);'i",
        "'&(pound|#163);'i",
        "'&(copy|#169);'i",
//        "'&#(\d+);'e"
    ];

    $replace = [
        "",
        "",
        "\\1",
        "\"",
        "&",
        "<",
        ">",
        " ",
        chr(161),
        chr(162),
        chr(163),
        chr(169),
//        "chr(\\1)"
    ];

    $text = preg_replace($search, $replace, $html);

    return iconv_substr($text, 0, $length, 'UTF-8');
}

/**
 * 移除富文本中的javascript
 * @param  string $html 富文本
 * @return string       移除javascript的富文本
 */
function remove_js($html)
{
    $pattern = '/<script[^>]*?>.*?</script>/si';
    $replacement = '';

    return preg_replace($pattern, $replacement, $html);
}

/**
 * 把html转成适合手机的html
 * @param  string $html 富文本
 * @return string       转换后的富文本
 */
function html_to_mobile($html)
{
    preg_match_all('/<img[^>]*? src="(.*?)"[^>]*?>/si', $html, $matches);

    $old_images = $new_images = $image_holders = [];
    $mb_content = $html;
    foreach ($matches[0] as $idx => $value) {
        $old_images[] = $value;
        $new_images[] = '<p><img src="' . $matches[1][$idx] . '"></p>';
        $image_holders[] = '##image_holder_'.$idx.'##';

        // 把图片变成占位符（preg_replace有bug替换没成功）
        $mb_content = str_replace($value, '##image_holder_'.$idx.'##', $mb_content);
    }

    $mb_content = preg_replace('|<p>\s*(<br/?>\s*)*</p>|', '##enter_holder##', $mb_content);
    $mb_content = preg_replace('|</p>\s*<p>|', '##enter_holder##', $mb_content);

    // 剔除所有html标签
    $mb_content = strip_tags($mb_content);

    $paragraphs = preg_split("/##image_holder_[\d]+##/", $mb_content);
    foreach ($paragraphs as $paragraph) {
        if (trim($paragraph) == '' || $paragraph == '&nbsp;') {
            continue;
        }

        // 还要用回车再分割一次
        // $sub_paragraphs = explode('##enter_holder##', $paragraph);
        // foreach ($sub_paragraphs as $sub_paragraph) {
        //     if (trim($sub_paragraph) == '' || $sub_paragraph == '&nbsp;') {
        //         continue;
        //     }

        //     $mb_content = str_replace($sub_paragraph, '<p class="pure-text">'.$sub_paragraph.'</p>', $mb_content);
        // }

        $mb_content = str_replace($paragraph, '<p class="pure-text">'.$paragraph.'</p>', $mb_content);
    }

    $mb_content = str_replace('##enter_holder##', '<br><br>', $mb_content);

    // 把新图片替换回去
    $patterns = array_map(function($item) {
        return "|$item|";
    }, $image_holders);
    $mb_content = preg_replace($patterns, $new_images, $mb_content);

    return $mb_content;
}

function font_size($max, $hot)
{
    $k = (30 - 10) / ($max - 1);

    return $k * $hot + 10;
}

/**
 * 生成分页的html
 */
function render_pagination($page, $limit, $total)
{
    $maxPage = ceil($total / $limit);

    if ($maxPage < 1) {
        return;
    }

    $html = '<div class="clearfix">';

    // 跳转
    $html .= '<ul class="pagination pagination-lg pull-right">';
    $html .= '<li><span>共 <span>' . $maxPage . '</span> 页( <span>' . $total . '</span> 条)</span></li>';
    $html .= '<li class="page-to"><span >跳至 <input type="tex" name="page"> 页</span></li>';
    $html .= '<li><input type="submit" class="goto" value="跳转"></li></ul>';

    // 分页start
    $html .= '<ul class="pagination pagination-lg pull-right">';

    // 前一页按钮
    if ($page > 1) {
        $html .= '<li><a href="' . change_page($page - 1) . '">&laquo;</a></li>';
    }

    $showPage = 5;      // 必须是奇数
    $halfNumber = floor($showPage / 2);     // 2

    if ($maxPage <= $showPage) {
        $start_page = 1;
        $end_page = $maxPage;
    } else {
        if ($page <= $halfNumber + 1) {
            $start_page = 1;
            $end_page = $showPage;
        } elseif ($page >= $maxPage - $halfNumber) {
            $start_page = $maxPage - $showPage + 1;
            $end_page = $maxPage;
        } else {
            $start_page = $page - $halfNumber;
            $end_page = $page + $halfNumber;
        }
    }

    // 生成分页
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $page) {
            $html .= '<li class="active"><a href="' . change_page($i) . '">' . $i . '</a></li>';
        } else {
            $html .= '<li><a href="' . change_page($i) . '">' . $i . '</a></li>';
        }
    }

    // 后一页按钮
    if ($page < $maxPage) {
        $html .= '<li><a href="' . change_page($page + 1) . '">&raquo;</a></li></li>';
    }

    $html .= "</ul></div>";

    echo $html;
}

/**
 * 生成分页的html
 */
function simple_pagination($page, $limit, $total)
{
    $maxPage = ceil($total / $limit);

    if ($maxPage < 1) {
        return;
    }

    $html  = '<ul class="pager"><nav>';

    if ($page > 1) {
        $html .= '<li class="previous"><a href="' . change_page($page - 1) . '">« 上一页</a></li>';
    }

    if ($page < $maxPage) {
        $html .= '<li class="next"><a href="' . change_page($page + 1) . '">下一页 »</a></li>';
    }

    $html .= '</ul></nav>';

    echo $html;
}

/**
 * 更改url里的page值
 */
function change_page($page)
{
    return change_url_arg($_SERVER['REQUEST_URI'], 'page', $page);
}

/**
 * 更改url里的某个值
 */
function change_url_arg($url, $arg_name, $arg_val)
{
    $pattern = '/' . $arg_name . '=([^&]*)/';
    $replace = $arg_name . '=' . $arg_val;

    if (preg_match($pattern, $url)) {
        $tmp = '/(' . $arg_name . '=)([^&]*)/i';
        $tmp = preg_replace($tmp, $replace, $url);
        return $tmp;
    } else {
        if (preg_match('[\?]', $url)) {
            return $url . '&' . $replace;
        } else {
            return $url . '?' . $replace;
        }
    }
}

function rand_len_int($len = 6)
{
    $min_value = pow(10, $len - 1);
    $max_value = pow(10, $len) - 1;

    return rand($min_value, $max_value);
}

function ellipsis_len($text, $length = 20)
{
    if (mb_strlen($text, 'UTF-8') <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length, 'UTF-8') . '...';
}

function is_sensitive($word)
{
    $word = mb_strtolower($word);
    $word = strtr($word, ['ｂ' => 'b', 'ａ' => 'a', 'ｎ' => 'n', '　' => '', ' ' => '']);

    $pattern  = ['/官方/', '/ban/'];
    $replace  = array_fill(0, count($pattern), '');
    $replaced = preg_replace($pattern, $replace, $word);

    return $replaced != $word;
}

/**
 * 当前用户与目标用户的关系
 * @param $user_id
 * @param $target_id
 * @return mixed
 */
function get_user_status($user_id, $target_id)
{
    if ($user_id == $target_id) {
        $status['is_me'] = true;
        $status['is_follow'] = $status['is_friend'] = false;
    } else {
        $follow_status = \DB::table('circle_follow_user_mst')
                        ->where('user_id', $user_id)
                        ->where('target_id', $target_id)
                        ->first();

        $fans_status = \DB::table('circle_follow_user_mst')
                        ->where('user_id', $target_id)
                        ->where('target_id', $user_id)
                        ->first();

        $status['is_me'] = false;
        $status['is_follow'] = (boolean) $follow_status;
        $status['is_friend'] = ((boolean) $follow_status) && ((boolean) $fans_status);
    }

    return $status;
}

// 获得一个并发锁，默认愿意为取锁付出10秒等待，锁在没人释放时15秒过期
function acquire_redis_lock($lockname, $identifier, $wait_time = 5, $timeout = 20)
{
    // 预计付出的时间成本后放弃取锁
    $end = time() + $wait_time;

    while($end > time()) {
        if (Redis::setnx($lockname, $identifier)) {
            Redis::expire($lockname, $timeout); # set expire time
            return $identifier;
        }

        // 等待 5ms
        usleep(5000);
    }

    return false;
}

// 释放一个并发锁
function release_redis_lock($lockname, $identifier)
{
    try {
        Redis::pipeline(function ($pipe) {
            $pipe->watch($lockname);

            if ($pipe->get($lockname) == $identifier) {
                $pipe->multi();
                $pipe->delete($lockname);
                $pipe->execute();

                return true;
            }

            $pipe->unwatch();
        });
    } catch (Exception $e) {
        return false;
    }

    return false;
}

// 获得一个并发锁，默认愿意为取锁付出5秒等待，锁在没人释放时20秒过期
function easy_lock($lock, $password, $wait_time = 5, $timeout = 20)
{
    // 预计付出的时间成本后放弃取锁
    $end = time() + $wait_time;

    while($end > time()) {
        if (Redis::setnx($lock, $password)) {
            Redis::expire($lock, $timeout); # set expire time
            return true;
        }

        // 等待 5ms
        usleep(5000);
    }

    return false;
}

function easy_unlock($lock, $password)
{
    if (Redis::get($lock) == $password) {
        Redis::del($lock);
    }
    // try { 这里感觉没变要用事务
        // Redis::pipeline(function ($pipe) {
        //     $pipe->watch($lock);

        //     if ($pipe->get($lock) == $password) {
        //         $pipe->multi();
        //         $pipe->del($lock);
        //         $pipe->exec();

        //         return true;
        //     }

        //     $pipe->unwatch();
        // });
    // } catch (Exception $e) {
    //     return false;
    // }

    // return false;
}

function own_easy_lock($lock, $password)
{
    if (Redis::get($lock) == $password) {
        return true;
    }

    return false;
}

/**
*定制流程
*/
function order_flow($data)
{
    $validator = Validator::make($data, [
        //订单id
        'order_id'      => 'required|integer',
        //用户id
        'op_user_id'    => 'integer',
        //要显示的信息
        'op_log'        => 'required|string|min:3|max:255',
        //本次操作相关的关键信息,json化的数组,
        /*['type' => 'logistics',
        'waybill_no' => Request::input('waybill_no'),]*/
        'op_info'       => 'array',
    ]);

    if ($validator->fails()) {
        throw new Exception($validator->errors()->first());
    }

    // if (!isset($data['op_user_id'])) {
    //     $data['op_user_id'] = 0; // 0为系统默认，也是系统操作
    // }

    if (isset($data['op_info'])) {
        $data['op_info'] = json_encode($data['op_info']);
    }

    Modules\Oms\Models\OrderFlow::create($data);
}


/**
 * 生成错误返回信息
 * @param  [type] $code    [description]
 * @param  [type] $message [description]
 * @return [type]          [description]
 */
function error_json($code, $message)
{
    $jsonMsg = [];
    $jsonMsg['status'] = $code;
    $jsonMsg['message'] = $message;
    return $jsonMsg;
}


/**
 * 数字取反
 * @param $a
 * @return float|int
 */
function turn($a)
{
    return $a > 0 ? -1 * $a : abs($a);
}

/**
 * 字符串处理
 */
function filter_string($str)
{
    return htmlspecialchars( trim($str) );
}


/**
 *判断姓名是否全是中文
 */
function is_all_chinese($str){
    if(strpos($str,'·')){
        $str=str_replace("·",'',$str);
    }
    return preg_match('/^[\x7f-\xff]+$/', $str) ? true : false;
}

/**
 * 验证身份证号
 */
function is_china_idcard($id)
{

    $area       = [
        11, 12, 13, 14, 15, 21, 22, 23, 31, 32,
        33, 34, 35, 36, 37, 41, 42, 43, 44, 45,
        46, 50, 51, 52, 53, 54, 61, 62, 63, 64,
        65, 71, 81, 82, 91, 100, 101, 103, 110
    ];
    $aWeight    = [
        7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2,6,1,3
    ];
    $aValidate  = [
        '1','0','X','9','8','7','6','5','4','3','2'
    ];
    $index      = substr($id,0,2);
    $id17       = substr($id,0,17);
    $sum        = 0;
    $len        = strlen($id);
    $len17      = strlen($id17);
    $rPattern   = '/^(([0-9]{2})|(19[0-9]{2})|(20[0-9]{2}))-((0[1-9]{1})|(1[012]{1}))-((0[1-9]{1})|(1[0-9]{1})|(2[0-9]{1})|3[01]{1})$/';

    for ($i=0; $i<$len17; $i++) {
        $sum    += $id17[$i] * $aWeight[$i];
    }
    $mode       = $sum % $area[0];

    if(!in_array($index, $area))                                return false;

    if($len == 18){
        $iDate      =  substr($id,6,4) . '-' . substr($id,10,2) . '-' . substr($id,12,2);
        if (!preg_match($rPattern, $iDate))                     return false;
        if (strtoupper($aValidate[$mode]) == substr($id,17,1))  return true;
        return false;
    }
    elseif($len == 15)
    {
        $iDate      =  '19'.substr($id,6,2) . '-' . substr($id,8,2) . '-' . substr($id,10,2);
        if(!preg_match($rPattern, $iDate))                      return false;
        if(!is_numeric($id))                                    return false;
        return true;
    }
    return false;
}

/**
 * 鉴定帖子的级别号
 * @param $is_specialty
 * @param int $price
 * @return int
 */
function appr_post_level($is_specialty,$price = 0)
{
    if ($is_specialty == 0) {//免费鉴定
        return  0;
    }elseif ($is_specialty == 1 && $price == 0 ) {//专业鉴定
        return 10;
    }elseif ($is_specialty == 1 && $price > 0 && $price < config('const.appr_price')) {//保价0-2000
        return 20;
    }elseif ($is_specialty == 1 && $price > config('const.appr_price')) {//保价大于2000
        return 30;
    }
}

