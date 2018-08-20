<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Models;
use Modules\Product\Models as ProductModels;
use Modules\Circle\Models  as CircleModels;
use Modules\Cms\Models     as CmsModels;
use DB;
use Request;
use Validator;
use JWTAuth;

class FavoriteController extends \BaseController
{
    const LIMIT_PER_PAGE = 10;

    /**
     * 这个收藏列表可能要废止，转用“装备，动态，文章”的搜索
     */
    public function index($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            if ($id != JWTAuth::user()->id) {
                abort(403, '没有权限，拒绝访问');
            }

            // 业务逻辑
            if (in_array(Request::input('type'), ['product', 'moment', 'article'])) {
                $item_type = Request::input('type');
            } else {
                $item_type = "product";
            }

            if (is_numeric(Request::input('page'))) {
                $page = Request::input('page');
            } else {
                $page = 1;
            }

            if (Request::input('type') == 'moment') {
                $take_num = 10;
            } else {
                $take_num = 20;
            }

            $item_id_arr = Models\UserFavorite::where('user_id', $id)
                                              ->where('item_type', $item_type)
                                              ->skip($take_num * ($page - 1))
                                              ->take($take_num)
                                              ->pluck('item_id')
                                              ->toArray();

            if ($item_type == 'product') {
                $items = ProductModels\ItemSku::whereIn('id', $item_id_arr)->get();

                // 整理json返回数据
                $items->each(function($item) {
                    $item->setVisible(['id', 'name', 'cover_image']);
                });
            } elseif ($item_type == 'moment') {
                $items = CircleModels\Moment::search(['favorite' => $item_id_arr]);
                $items->each(function ($item) use($id) {
                    $item->content_image = json_decode($item->content_image, true);
                    $item->user_name = $item->user->name;
                    $item->user_avatar = $item->userProfile->avatar ? $item->userProfile->avatar : '';
                    $item->topic_name = $item->topic ? $item->topic->name : null;
                    foreach ($item->tags as $key => $value) {
                        unset($item->tags[$key]->alias_name);
                        unset($item->tags[$key]->sort_order);
                        unset($item->tags[$key]->created_at);
                        unset($item->tags[$key]->updated_at);
                        unset($item->tags[$key]->deleted_at);
                        unset($item->tags[$key]->pivot);
                    }

                    $is_like = CircleModels\Moment::whereHas('userLike', function($query) use($item, $id) {
                        $query->where('moment_id', $item->id)->where('user_id', $id);
                    })->get();

                    $item->is_like = !($is_like->isEmpty());
                    $item->is_favorite = true;

                    unset($item->user);
                    unset($item->userProfile);
                    unset($item->topic);
                });
            } elseif ($item_type == 'article') {
                $items = CmsModels\Article::whereIn('id', $item_id_arr)->get();

                // 整理json返回数据
                $items->each(function($item) {
                    $item->user_name = $item->user->name;
                    $item->category_name = $item->category->name;
                    unset($item->user);
                    unset($item->category);
                    $item->setVisible(['id', 'user_id', 'title', 'cover_image', 'comment_cnt', 'publish_time', 'user_name', 'category_id','category_name']);
                });
            }

            $res['data'] = $items;

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
/*
    public function store($id)
    {
        if ($id != JWTAuth::user()->id) {
            abort(500, '非法操作，不能更新');
        }

        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'id'        => 'require|numeric',
            'type'      => 'require|in:product,moment,article',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            // XXX:此处不验证item_id是否存在，如果没有显示时当收藏失效处理

            // 业务逻辑
            $new_item = Models\UserFavorite::create([
                'user_id'   => $id,
                'item_type' => Request::input('type'),
                'item_id'   => Request::input('id'),
            ]);

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
 */
}
