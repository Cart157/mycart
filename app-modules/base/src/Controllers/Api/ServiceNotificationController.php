<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Models;
use Request;
use JWTAuth;

class ServiceNotificationController extends \BaseController
{
    const LIMIT_PER_PAGE = 10;

    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            // 通过token获取user_id
            $user_id = JWTAuth::user()->id;
            $limit   = (int) Request::input('limit') ?: self::LIMIT_PER_PAGE;

            $notifications = Models\ServiceNotification::where('user_id', $user_id)
                ->orderBy('id', 'desc')
                ->paginate($limit);

            // 整理返回数据
            $notifications->each(function($item) {
                $item->addHidden(['user_id', 'tpl_id', 'updated_at', 'deleted_at']);
                $item->message_info = json_decode($item->message_info, true);
            });

            $res['data'] = $notifications->items();

            // 标记为已读
            $is_read_list = $notifications->pluck('id')->all();
            Models\ServiceNotification::whereIn('id', $is_read_list)
                ->update(['is_read' => 1]);

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
