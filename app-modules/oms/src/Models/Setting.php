<?php

namespace Modules\Oms\Models;

use Backpack\CRUD\CrudTrait;

class Setting extends \BaseModel
{
    use CrudTrait;

    protected $table = 'oms_setting';
    protected $guarded = [];
}
