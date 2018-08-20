<?php

namespace Modules\Mall\Models;

use Backpack\CRUD\CrudTrait;
use Modules\Base\Traits\Model\TreeViewTrait;

class Category extends \BaseModel
{
    use CrudTrait;
    use TreeViewTrait;

    protected $table = 'mall_category';
    protected $fillable = ['name', 'parent_id', 'type_id', 'sort_order', 'cover_image'];

    public function parent()
    {
        return $this->belongsTo('Modules\Mall\Models\Category', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('Modules\Mall\Models\Category', 'parent_id')->orderBy('sort_order');
    }

    public function offspring()
    {
        return $this::where('path', 'like', "$this->path%")->get();
    }

    public function type()
    {
        return $this->belongsTo('Modules\Mall\Models\Type', 'type_id');
    }

    public function setCoverImageAttribute($value)
    {
        $attribute_name = "cover_image";
        $disk = "uploads";

        if ($this->id) {
            $tmp = $this->id;
        } else {
            $tmp = '_tmp';
        }

        $destination_path = "mall/category/".$tmp;

        $this->uploadFileToDisk($value, $attribute_name, $disk, $destination_path);
        $this->attributes[$attribute_name] = sprintf('/%s/%s', $disk, $this->attributes[$attribute_name]);
    }

    public function getCoverImageHtml()
    {
        return sprintf('<img src="%s" height="100">', $this->cover_image);
    }
}