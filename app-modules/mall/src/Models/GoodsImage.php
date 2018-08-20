<?php

namespace Modules\Mall\Models;

use Backpack\CRUD\CrudTrait;

class GoodsImage extends \BaseModel
{
    use CrudTrait;

    protected $table = 'mall_goods_image';
    protected $fillable = ['spu_id', 'sku_id', 'color_id', 'path', 'sort_order', 'is_default'];

    public function spu()
    {
        return $this->belongsTo('Modules\Mall\Models\GoodsSpu', 'spu_id');
    }

    public function sku()
    {
        return $this->belongsTo('Modules\Mall\Models\Goods', 'sku_id');
    }

    public function getImageHtml()
    {
        return sprintf('<img src="%s" height="100">', $this->path);
    }
}