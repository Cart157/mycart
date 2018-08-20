<?php

namespace Modules\Oms\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderExpansion extends \BaseModel
{
    use CrudTrait;
    use SoftDeletes;

    public $incrementing        = false;    // 无递增主键
    public $timestamps          = false;    // 不维护时间戳
//    protected $forceDeleting    = true;     // 禁止删除时 set deleted_at
    protected $primaryKey       = 'order_id';// 可以通过 find() 查找

    protected $table = 'oms_order_expansion';
    protected $fillable = ['order_id', 'consignee_name', 'consignee_info', 'order_remark', 'deliver_explain', 'memo', 'promotion_info', 'promotion_amount', 'consigner_delivery_id', 'consigner_refund_id'];

    // 禁止查询时串 deleted_at is null
    public static function bootSoftDeletes()
    {
        // 覆盖trait SoftDeletes里的方法
        // static::addGlobalScope(new SoftDeletingScope);
    }
}
