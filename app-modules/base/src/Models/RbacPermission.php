<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class RbacPermission extends \BaseModel
{
    use CrudTrait;

    protected $table    = 'base_rbac_permission';
    protected $fillable = ['name', 'title', 'parent_id', 'updated_at', 'created_at'];

    public function parent()
    {
        return $this->belongsTo('Modules\Base\Models\RbacPermission', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('Modules\Base\Models\RbacPermission', 'parent_id');
    }

    public function roles()
    {
        return $this->belongsToMany('Modules\Base\Models\RbacRole', 'base_rbac_role_permission_mst', 'permission_id', 'role_id');
    }

    public function users()
    {
        return $this->belongsToMany('Modules\Base\Models\User', 'base_rbac_user_permission_mst', 'permission_id', 'user_id');
    }

    public $tmp_tree_str;
    public function getTitleHtml($depth = 0, $item = null)
    {
        $item = $item ?: $this;
        if ($depth == 0) {
            $this->tmp_tree_str = '　├　';
            if ($item->isLast()) {
                $this->tmp_tree_str = '　└　';
            }
        }

        if ($item->parent) {
            if ($item->parent->isLast()) {
                $this->tmp_tree_str = '　　　' . $this->tmp_tree_str;
            } else {
                $this->tmp_tree_str = '　│　' . $this->tmp_tree_str;
            }

            $this->getTitleHtml($depth + 1, $item->parent);
        }

        return $this->tmp_tree_str . $this->title;
    }

    public function isLast()
    {
        $last_node = $this->query()->where('parent_id', $this->parent_id)->get()->last();
        return $last_node->id == $this->id;
    }

    public $tmp_tree_ids;
    public $tmp_tree_arr;
    public function getPermissionTree($to_array = false)
    {
        // 转成多维数组
        if ($to_array) {
            $this->tmp_tree_arr = [];
        } else {
            $this->tmp_tree_ids = [];
        }

        $top_items = $this->where('parent_id', 0)->get();
        $this->makeTree($top_items);

        return $to_array ? $this->tmp_tree_arr : $this->tmp_tree_ids;
    }

    protected function makeTree($items, $depth = 0)
    {
        $children = [];
        foreach ($items as $item) {
            // 转成多维数组
            if (!is_null($this->tmp_tree_arr)) {
                $arr_item = [
                    'id' => $item->id,
                    'text' => $item->title,
                ];

                if ($item->children->count() > 0) {
                    $arr_item['children'] = $this->makeTree($item->children, $depth + 1);
                }

                if ($depth == 0) {
                    $this->tmp_tree_arr[] = $arr_item;
                } else {
                    $children[] = $arr_item;
                }
            } else {
                $this->tmp_tree_ids[] = $item->id;
                if ($item->children->count() > 0) {
                    $this->makeTree($item->children, $depth + 1);
                }
            }
        }

        return $children;
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
