<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class ServiceNotification extends \BaseModel
{
    use CrudTrait;

    const LIMIT_PER_PAGE = 10;

    protected $table = 'base_service_notification';
    protected $guarded = [];


    // ========================================
    // for 关系
    // ========================================
    public function user()
    {
        return $this->belongsTo('Modules\Base\Models\User', 'user_id')->select(['id', 'name', 'avatar'])
                    ->leftJoin('base_user_profile', 'base_user_profile.user_id', '=', 'base_user.id');
    }

    public function tpl()
    {
        return $this->belongsTo('Modules\Base\Models\ServiceNotificationTpl', 'tpl_id');
    }
}
