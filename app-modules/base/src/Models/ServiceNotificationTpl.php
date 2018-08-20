<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class ServiceNotificationTpl extends \BaseModel
{
    use CrudTrait;

    protected $table   = 'base_service_notification_tpl';
    protected $guarded = [];
}
