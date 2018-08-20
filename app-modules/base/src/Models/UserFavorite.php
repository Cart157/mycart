<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class UserFavorite extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_user_favorite';
    protected $guarded = [];
}
