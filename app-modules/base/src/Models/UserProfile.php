<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class UserProfile extends \BaseModel
{
    use CrudTrait;

    public $incrementing        = false;    // 无递增主键
    public $timestamps          = false;    // 不维护时间戳
    protected $forceDeleting    = true;     // 禁止删除时 set deleted_at
    protected $primaryKey       = 'user_id';// 可以通过 find() 查找

    protected $table = 'base_user_profile';
    protected $fillable = ['user_id', 'summary', 'avatar', 'cover', 'sex', 'birthday', 'location',
        'mobile', 'tel', 'qq', 'weixin', 'weibo', 'taobao', 'leancloud'
    ];

    // ========================================
    // for 关系
    // ========================================
    public function user()
    {
        return $this->belongsTo('Modules\Base\Models\User');
    }


    // ========================================
    // for make
    // ========================================
    public static function makeImUser($user)
    {
        do {
            $im_user = 'bu-'. str_rand(10);
            // $im_user = 'bu-'. sprintf('%010s', $user->id);
            $exists = self::where('im_user', $im_user)->first();
        } while ($exists);

        return $im_user;
    }


    // ========================================
    // for 软删除
    // ========================================
    // 禁止查询时串 deleted_at is null
    public static function bootSoftDeletes()
    {
        // 覆盖trait SoftDeletes里的方法
        // static::addGlobalScope(new SoftDeletingScope);
    }
}
