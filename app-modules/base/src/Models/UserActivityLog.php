<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class UserActivityLog extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_user_activity_log';
    protected $fillable = ['user_id', 'actvity_id', 'is_win'];
}
