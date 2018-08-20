<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;
use DB;

class PlatformUser extends \BaseModel
{
    use CrudTrait;

    const LIMIT_PER_PAGE = 10;

    protected $table = 'base_platform_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'is_used', 'create_user'
    ];

    
    //user
    public function user()
    {
        return $this->hasOne('Modules\base\Models\User','id','user_id');
    }

    
}
