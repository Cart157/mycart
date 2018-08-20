<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class Task extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_task';
    protected $fillable = ['name', 'description', 'max_coin', 'sort_order'];
}
