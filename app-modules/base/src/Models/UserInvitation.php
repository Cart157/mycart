<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class UserInvitation extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_user_invitation';
    protected $fillable = [
        'user_id', 'new_mobile', 'is_activated',
    ];
}
