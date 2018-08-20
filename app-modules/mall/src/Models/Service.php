<?php

namespace Modules\Mall\Models;

use Backpack\CRUD\CrudTrait;

class Service extends \BaseModel
{
    use CrudTrait;

    protected $table = 'mall_service';
    protected $fillable = ['name', 'image', 'sort_order'];

    public function setImageAttribute($value)
    {
        $attribute_name = "image";
        $disk = "uploads";

        $destination_path = "mall/service";
        $this->uploadFileToDisk($value, $attribute_name, $disk, $destination_path);

        $this->attributes[$attribute_name] = sprintf('/%s/%s', $disk, $this->attributes[$attribute_name]);
    }

    public function getImageHtml()
    {
        return sprintf('<img src="%s" height="100">', $this->image);
    }
}
