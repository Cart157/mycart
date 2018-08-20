<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class Banner extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_banner';
    protected $guarded = [];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function type()
    {
        return $this->belongsTo('Modules\Base\Models\FunType', 'type_id');
    }

    public function setImageAttribute($value)
    {
        $attribute_name = 'image';
        $disk = 'uploads';

        $destination_path = 'custom/banner/'.$this->attributes['item_id'];
        $this->uploadFileToDisk($value, $attribute_name, $disk, $destination_path);

        $this->attributes[$attribute_name] = sprintf('/%s/%s', $disk, $this->attributes[$attribute_name]);


    }

    public function getImageHtml()
    {
        return sprintf('<img src="%s" height="100">', admin_cdn().$this->image);
    }
}
