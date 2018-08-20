<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class UserReport extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_user_report';
    protected $guarded = [];
}
