<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class Setting extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_setting';
    protected $fillable = ['key', 'value', 'memo'];
}
