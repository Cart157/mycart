<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class FunType extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_fun_type';
    protected $guarded = [];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
}
