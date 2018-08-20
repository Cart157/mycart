<?php

namespace Modules\Mall\Models;

use Backpack\CRUD\CrudTrait;
use Modules\Base\Traits\Model\TreeViewTrait;

class Brand extends \BaseModel
{
    use CrudTrait;
    use TreeViewTrait;
/*
    const BRAND_CODE  = 0;
    const SERIES_CODE = 1;
    const LIMIT_PER_PAGE = 10;
 */
    protected $table = 'mall_brand';
    protected $fillable = ['cloud_brand_id', 'name', 'parent_id', 'type', 'initial', 'image', 'sort_order'];

    public function parent()
    {
        return $this->belongsTo('Modules\Mall\Models\Brand', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('Modules\Mall\Models\Brand', 'parent_id')->orderBy('sort_order');
    }
/*
    public function setImageAttribute($value)
    {
        $attribute_name = "image";
        $disk = "uploads";

        $destination_path = "mall/brand/".$this->id;
        $this->uploadFileToDisk($value, $attribute_name, $disk, $destination_path);

        $this->attributes[$attribute_name] = sprintf('/%s/%s', $disk, $this->attributes[$attribute_name]);
    }
 */
    public function getImageHtml()
    {
        return sprintf('<img src="%s" height="100">', $this->image);
    }
/*
    public function search($condition, $is_series = false)
    {
        // parent_id
        // brand_id
        // wd & keyword
        // limit
        // page
        $q = $this->query();

        if ($is_series) {
            $q->select('id', 'name');
            $q->where('type', self::SERIES_CODE);
        } else {
            $q->select('id', 'name', 'image', 'initial');
            $q->where('type', self::BRAND_CODE);
        }

        // parent_id
        if (isset($condition['parent_id'])) {
            $q->where('parent_id', $condition['parent_id']);
        }

        // brand_id
        if (isset($condition['brand_id'])) {
            $q->where('parent_id', $condition['brand_id']);
        }

        // wd
        if (isset($condition['wd'])) {
            if ($is_series) {
                $q->where('name', 'like', '%'.$condition['wd'].'%');
            } else {
                $q->where('alias_name', 'like', '%'.$condition['wd'].'%');
            }
        }

        // limit
        $take_num = self::LIMIT_PER_PAGE;
        if (isset($condition['limit'])) {
            $take_num = (int) $condition['limit'];

            $q->take($take_num);
        }

        // page
        if (isset($condition['page'])) {
            $skip_num = $take_num * ($condition['page'] - 1);

            $q->skip($skip_num)
              ->take($take_num);
        }

        return $q->orderBy('sort_order', 'asc')->get();
    }
*/
}
