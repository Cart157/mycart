<?php

namespace Modules\Mall\Models;

use Backpack\CRUD\CrudTrait;

class Cart extends \BaseModel
{
    use CrudTrait;

    protected $table = 'mall_cart';
    protected $fillable = ['name'];


    // 关系模型
    public function user()
    {
        return $this->belongsTo('Modules\Base\Models\User');
    }

    public function goods()
    {
        return $this->belongsTo('Modules\Mall\Models\Goods', 'goods_id');
    }
}
