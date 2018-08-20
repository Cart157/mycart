<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;
use Modules\Base\Traits\Model\TreeViewTrait;

class RbacRole extends \BaseModel
{
    use CrudTrait;
    use TreeViewTrait;

    protected $table    = 'base_rbac_role';
    protected $fillable = ['name', 'slug', 'parent_id', 'remark', 'updated_at', 'created_at'];

    public function parent()
    {
        return $this->belongsTo('Modules\Base\Models\RbacRole', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('Modules\Base\Models\RbacRole', 'parent_id');
    }

    public function permissions()
    {
        return $this->belongsToMany('Modules\Base\Models\RbacPermission', 'base_rbac_role_permission_mst', 'role_id', 'permission_id');
    }

    public function users()
    {
        return $this->belongsToMany('Modules\Base\Models\User', 'base_rbac_user_role_mst', 'role_id', 'user_id');
    }

    // 禁止删除时 set deleted_at
    protected $forceDeleting    = true;
    // 禁止查询时查 deleted_at is null
    public static function bootSoftDeletes()
    {
        // 覆盖trait SoftDeletes里的方法
        // static::addGlobalScope(new SoftDeletingScope);
    }
}
