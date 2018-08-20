<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class UserCash extends \BaseModel
{
    use CrudTrait;

    const LIMIT_PER_PAGE = 10;

    protected $table = 'base_cash_apply';
    protected $guarded = [''];
    protected $hidden = ['created_at','updated_at','deleted_at'];

    public function user()
    {
        return $this->belongsTo('Modules\Base\Models\User','user_id')
                    ->select('id','name','avatar','alipay_account','alipay_realname')
                    ->join('base_user_profile','base_user_profile.user_id','=','base_user.id');
    }


}
