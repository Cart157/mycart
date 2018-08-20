<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Models;
use Modules\Circle\Models as CModels;
use Modules\Cms\Models as CmsModels;
use Modules\Appraisal\Models as AModels;
use Modules\Product\Models as PModels;
use Modules\Activity\Models as ActivityModels;
use Request;
use JWTAuth;

class SystemNotificationController extends \BaseController
{
    const LIMIT_PER_PAGE = 20;

    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            // 通过token获取user_id
            $user_id = JWTAuth::user()->id;

            $limit = (int) Request::input('limit') ?: self::LIMIT_PER_PAGE;
            $notices = Models\SystemNotification::where('user_id', $user_id)
                                          ->with('from_user')
                                          ->orderBy('created_at', 'desc')
                                          ->paginate($limit);

            // 整理返回数据
            $notices->each(function($item) {
                $item->addHidden(['from_user_id', 'updated_at', 'deleted_at']);
                $item->extra = json_decode($item->extra, true);
            });

            $res['data'] = $notices->items();

            // 标记为已读
            $is_read_list = $notices->pluck('id')->all();
            Models\SystemNotification::whereIn('id', $is_read_list)
                               ->update(['is_read' => 1]);

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function index2()
    {
        $res = parent::apiFetchedResponse();

        try {
            // 通过token获取user_id
            $user_id = JWTAuth::user()->id;
            $condition = array_merge(Request::all(), ['user_id' => $user_id]);

            $notice = Models\SystemNotification::search($condition)->each(function ($item) use ($user_id) {
                $extra = json_decode($item->extra, true);

                // 加图片、内容
                if (isset($extra['article_id']) && CmsModels\Article::find($extra['article_id'])) {
                    $extra['is_delete'] = false;
                    $extra['image'] = CmsModels\Article::find($extra['article_id'])->cover_image;
                } elseif (isset($extra['moment_id']) && CModels\Moment::find($extra['moment_id'])) {
                    $extra['is_delete'] = false;
                    $moment = CModels\Moment::select('content_image', 'content')->find($extra['moment_id']);
                    $extra['image'] = json_decode($moment->content_image, true)[0];
                    $extra['content'] = $moment->content;
                } elseif (isset($extra['post_id']) && AModels\Post::find($extra['post_id'])) {
                    $extra['is_delete'] = false;
                    $post = AModels\Post::select('image', 'cloud_sku_name', 'final_result')->find($extra['post_id']);
                    $extra['image'] = $post->image;
                    $extra['name'] = $post->cloud_sku_name;
                    $extra['result'] = $post->final_result;
                } elseif (isset($extra['sku_id']) && PModels\ItemSku::find($extra['sku_id'])) {
                    $extra['is_delete'] = false;
                    $extra['image'] = PModels\ItemSku::find($extra['sku_id'])->cover_image;
                } elseif (isset($extra['ext_activity_id']) && $activity = ActivityModels\Activity::find($extra['ext_activity_id'])) {
                    $extra['is_delete'] = false;

                    $extra['image'] = $activity->image;
                    $extra['name'] = $activity->name;
                } elseif (isset($extra['group_activity_id']) && CModels\GroupActivity::find($extra['group_activity_id'])) {
                    $extra['is_delete'] = false;
                    $activity = CModels\GroupActivity::select('cover_image', 'name')->find($extra['group_activity_id']);

                    $extra['image'] = $activity->cover_image;
                    $extra['name'] = $activity->name;
                } elseif (isset($extra['group_id']) && $group = CModels\Group::find($extra['group_id'])) {
                    $extra['is_delete'] = false;

                    $extra['image'] = $group->avatar;
                    $extra['name']  = $group->name;
                } elseif($extra != null) {
                    // 找不到id且extra不为空，设is_delete为true且name为null
                    $extra['is_delete'] = true;
                    $extra['name'] = $extra['image'] = $extra['content'] = null;
                }

                // 加评论、回复内容、点赞状态
                if (isset($extra['comment_id'])) {
                    if (isset($extra['article_id']) && CmsModels\ArticleComment::find($extra['comment_id'])) {
                        $extra['my_comment'] = CmsModels\ArticleComment::find($extra['comment_id'])->content;
                    } elseif (isset($extra['moment_id']) && CModels\MomentComment::find($extra['comment_id'])) {
                        $extra['my_comment'] = CModels\MomentComment::find($extra['comment_id'])->content;
                    } elseif (isset($extra['post_id']) && AModels\PostComment::find($extra['comment_id'])) {
                        $extra['my_comment'] = AModels\PostComment::find($extra['comment_id'])->content;
                    } elseif (isset($extra['sku_id']) && PModels\ItemComment::find($extra['comment_id'])) {
                        $extra['my_comment'] = PModels\ItemComment::find($extra['comment_id'])->content;
                    } else {
                        // 有comment_id但找不到，my_comment为null
                        $extra['my_comment'] = null;
                    }
                }

                // 加评论、回复内容、点赞状态
                if (isset($extra['item_id'])) {
                    if (isset($extra['article_id']) && CmsModels\ArticleComment::find($extra['item_id'])) {
                        $extra['comment'] = CmsModels\ArticleComment::find($extra['item_id'])->content;

                        $is_like = \DB::table('cms_like_article_comment_log')->where('user_id', $user_id)->where('article_comment_id', $extra['item_id'])->first();
                        $extra['is_like'] = (boolean) $is_like;
                    } elseif (isset($extra['moment_id']) && CModels\MomentComment::find($extra['item_id'])) {
                        $extra['comment'] = CModels\MomentComment::find($extra['item_id'])->content;

                        $is_like = \DB::table('circle_like_moment_comment_log')->where('user_id', $user_id)->where('moment_comment_id', $extra['item_id'])->first();
                        $extra['is_like'] = (boolean) $is_like;
                    } elseif (isset($extra['post_id']) && AModels\PostComment::find($extra['item_id'])) {
                        $extra['comment'] = AModels\PostComment::find($extra['item_id'])->content;

                        $is_like = \DB::table('appr_like_post_comment_log')->where('user_id', $user_id)->where('post_comment_id', $extra['item_id'])->first();
                        $extra['is_like'] = (boolean) $is_like;
                    } elseif (isset($extra['sku_id']) && PModels\ItemComment::find($extra['item_id'])) {
                        $extra['comment'] = PModels\ItemComment::find($extra['item_id'])->content;

                        $is_like = \DB::table('product_like_item_comment_log')->where('user_id', $user_id)->where('item_comment_id', $extra['item_id'])->first();
                        $extra['is_like'] = (boolean) $is_like;
                    } else {
                        // 有item_id但找不到，comment为null
                        $extra['comment'] = null;
                        $extra['is_like'] = false;
                    }
                }

                $item->extra = $extra;
                $follow_status = \DB::table('circle_follow_user_log')
                                ->where('user_id', $user_id)
                                ->where('target_id', $item->from_user_id)
                                ->first();

                $fans_status = \DB::table('circle_follow_user_log')
                                ->where('user_id', $item->from_user_id)
                                ->where('target_id', $user_id)
                                ->first();

                $item->from_user->is_follow = (boolean) $follow_status;
                $item->from_user->is_friend = ((boolean) $follow_status) && ((boolean) $fans_status);
            });
            $res['data'] = $notice;

            // 标记为已读
            $is_read_list = $notice->pluck('id')->all();
            Models\SystemNotification::whereIn('id', $is_read_list)->update(['is_read' => 1]);

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function destroy($id)
    {
        $res = parent::apiDeletedResponse();

        try {
            // 业务逻辑
            $system_notice = Models\SystemNotification::findOrFail($id);

            if ($system_notice->user_id != JWTAuth::user()->id) {
                abort(500, '非法操作');
            }

            $system_notice->delete();

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
