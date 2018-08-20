<?php

namespace Modules\Oms\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderGoods extends \BaseModel
{
    use CrudTrait;
    use SoftDeletes;

    protected $table = 'oms_order_goods';
    protected $guarded = [];


    // ========================================
    // for 关系
    // ========================================
    public function order()
    {
        return $this->belongsTo('Modules\Oms\Models\Order');
    }

    public function src_goods()
    {
        // 如果找不到会是 null
        if ($this->goods_type == 'goods') {
            return $this->belongsTo('Modules\Mall\Models\Goods', 'goods_id');

        // 必然返回null
        } else {
            // 只是一个hack
            return $this->belongsTo('Modules\Mall\Models\Goods', 'goods_type');
        }
    }

    public function src_custom_log()
    {
        // 如果找不到会是 null
        if ($this->goods_type == 'custom_log') {
            return $this->belongsTo('Modules\Customization\Models\UserCustomLog', 'goods_id');

        // 必然返回null
        } else {
            // 只是一个hack
            return $this->belongsTo('Modules\Customization\Models\UserCustomLog', 'goods_type');
        }

    }

    public function src_care_log()
    {
        // 如果找不到会是 null
        if ($this->goods_type == 'care_log') {
            return $this->belongsTo('Modules\Care\Models\CareOrderLog', 'goods_id');

        // 必然返回null
        } else {
            // 只是一个hack
            return $this->belongsTo('Modules\Care\Models\CareOrderLog', 'goods_type');
        }

    }


    // ========================================
    // for 访问器 & 修改器
    // ========================================
    // 反序列化 goods_extra
    public function getGoodsExtraAttribute($value)
    {
        return json_decode($value, true);
    }
}
