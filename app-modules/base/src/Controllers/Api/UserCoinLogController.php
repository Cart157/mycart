<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Models;
use Request;
use JWTAuth;
use Carbon\Carbon;

class UserCoinLogController extends \BaseController
{
    const LIMIT_PER_PAGE = 10;

    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            //分页数
            $limit = (int) Request::input('limit') ?: self::LIMIT_PER_PAGE;
            //用户id
            $user_id = JWTAuth::user()->id;

            //获取usercoinlog表中的数据：chagenumber！=0 为条件
            $log = Models\UserCoinLog::where('user_id', $user_id)->where('change_num', '<>', 0);

            //判断是否有要查的类型 income为收入  expense 为支出
            if (Request::has('type')) {
                if (Request::input('type') == 'income') {
                    //筛选收入
                    $log->where('change_num', '>', 0);
                } elseif (Request::input('type') == 'expense') {
                    //筛选支出
                    $log->where('change_num', '<', 0);
                }
            }

            //分页
            $log = $log->orderBy('id', 'desc')->paginate($limit)->items();

            //遍历log，把每一项都添加名称
            $res['data'] = array_map(function ($item) {
                //获取app-moudles/base/config/const.php/'coin_use_way'属性数组
                $use_way_name = config('const.coin_use_way');
                //获取app-moudles/base/config/const.php/'coin_get_way属性数组
                $get_way_name = config('const.coin_get_way');
                //如果get——way——id不为空
                if (!is_null($item->get_way_id)) {
                    //获取对应的名称
                    $item->name = $get_way_name[$item->get_way_id]['name'];
                } elseif(!is_null($item->use_way_id)) {
                    $item->name = $use_way_name[$item->use_way_id]['name'];
                }
                //设置可见的项目
                $item->setVisible(['id', 'name', 'change_num', 'created_at']);

                return $item;
            }, $log);
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}