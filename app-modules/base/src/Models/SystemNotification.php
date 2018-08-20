<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;
use DB;

class SystemNotification extends \BaseModel
{
    use CrudTrait;

    const LIMIT_PER_PAGE = 10;

    protected $table = 'base_system_notification';
    protected $fillable = ['user_id', 'from_user_id', 'type', 'message', 'extra'];

    // 关系模型
    public function from_user()
    {
        return $this->belongsTo('Modules\Base\Models\User', 'from_user_id')->select(['id', 'name', 'avatar'])
                    ->leftJoin('base_user_profile', 'base_user_profile.user_id', '=', 'base_user.id');
    }

    public static function search($condition, $field = ['id', 'from_user_id', 'type', 'message', 'extra', 'is_read', 'created_at'])
    {
        $q = self::select($field)->where('user_id', $condition['user_id']);

        if (isset($condition['category'])) {
            if ($condition['category'] == 'system') {
                $q->whereIn('type', ['official', 'user-follow', 'appraisal', 'group_activity_remind', 'group_activity_join', 'group_activity_cancel', 'group-join', 'group-verify', 'ext-activity-start', 'ext-activity-draw', 'ext-activity-vote']);
            } elseif ($condition['category'] == 'like') {
                $q->whereIn('type', ['article-like', 'article-comment-like', 'moment-like', 'moment-comment-like', 'item-comment-like', 'post-comment-like']);
            } elseif ($condition['category'] == 'comment') {
                $q->whereIn('type', ['article-comment', 'moment-comment', 'post-comment']);
            } elseif ($condition['category'] == 'reply') {
                $q->whereIn('type', ['article-comment-reply', 'moment-comment-reply', 'item-comment-reply', 'post-comment-reply']);
            }
        }

        if (isset($condition['is_follow']) && $condition['is_follow'] == 'true') {
            $from_user_ids = DB::table('circle_follow_user_log')->where('user_id', $condition['user_id'])->pluck('target_id')->toArray();
            $q->whereIn('from_user_id', $from_user_ids);
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

        return $q->orderBy('created_at', 'desc')->get();
    }
}
