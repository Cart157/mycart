<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class CheckIn extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_checkin_log';
    protected $fillable = ['user_id', 'updated_at'];

    public function user()
    {
        return $this->belongsTo('Modules\Base\Models\User', 'user_id');
    }

    public function userProfile()
    {
        return $this->belongsTo('Modules\Base\Models\UserProfile', 'user_id', 'user_id');
    }
}
