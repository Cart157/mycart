<?php

namespace Modules\Mall\Models;

use Backpack\CRUD\CrudTrait;

class Topic extends \BaseModel
{
    use CrudTrait;

    const LIMIT_PER_PAGE = 10;

    protected $table = 'mall_topic';
    protected $fillable = ['name', 'image', 'sort_order'];

    public function goods()
    {
        return $this->belongsToMany('Modules\Mall\Models\Goods', 'mall_topic_goods_mst', 'topic_id', 'goods_id');
    }

    public function getImageHtml()
    {
        return sprintf('<img src="%s" height="100">', $this->image);
    }

    public function getGoodsCountHtml()
    {
        $url_index  = sprintf('%s?topic_id=%d', route('admin.mall.goods.index'), $this->id);
        return sprintf('<a href="%s">%d个</a>', $url_index, $this->goods->count());
    }

    public function setImageAttribute($value)
    {
        $attribute_name = "image";
        $disk = "uploads";
        $destination_path = "mall/topic";

        $this->uploadFileToDisk($value, $attribute_name, $disk, $destination_path);
        $this->attributes[$attribute_name] = sprintf('/%s/%s', $disk, $this->attributes[$attribute_name]);
    }
}
