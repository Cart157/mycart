<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class AppUpdateLog extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_app_update_log';

    protected $fillable = ['device_type', 'version', 'version_name', 'update_url', 'update_content', 'is_force', 'is_ignore'];
    protected $hidden   = ['id', 'created_at', 'updated_at', 'deleted_at'];
}
