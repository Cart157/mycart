<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class UserCoinLog extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_user_coin_log';
    protected $fillable = ['user_id', 'change_num', 'get_way_id', 'use_way_id', 'memo'];

    // 关系模型
    public function user()
    {
        return $this->belongsTo('Modules\Base\Models\User');
    }
}
