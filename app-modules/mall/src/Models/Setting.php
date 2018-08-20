<?php

namespace Modules\Mall\Models;

use Backpack\CRUD\CrudTrait;

class Setting extends \BaseModel
{
    use CrudTrait;

    protected $table = 'mall_setting';
    protected $fillable = ['key', 'value', 'memo'];
}
