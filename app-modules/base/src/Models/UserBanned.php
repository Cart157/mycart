<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class UserBanned extends \BaseModel
{
    use CrudTrait;

    const LIMIT_PER_PAGE = 10;

    protected $table = 'base_user_banned';
    protected $fillable = ['user_id', 'reason'];

    public function user()
    {
        return $this->belongsTo('Modules\Base\Models\User', 'user_id', 'id');
    }
}
