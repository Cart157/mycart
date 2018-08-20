<?php

namespace Modules\Oms\Models;

use Modules\Base\Models;
use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use DB;
use JWTAuth;

class Refund extends \BaseModel
{
    use CrudTrait;

    const LIMIT_PER_PAGE = 10;

    protected $table = 'oms_refund';
    protected $fillable = ['user_id', 'order_id', 'goods_id', 'refund_type', 'is_receive', 'reason', 'refund_amount', 'description', 'image',
        'seller_remark', 'new_order_id', 'refund_status'];

    // 关系模型
    public function user()
    {
        return $this->belongsTo('Modules\Base\Models\User', 'user_id');
    }

    public function order()
    {
        return $this->belongsTo('Modules\Oms\Models\Order', 'order_id');
    }

    public function newOrder()
    {
        return $this->belongsTo('Modules\Oms\Models\Order', 'new_order_id');
    }

    public function orderGoods()
    {
        return $this->hasMany('Modules\Oms\Models\OrderGoods', 'order_id', 'order_id');
    }

    public function detail()
    {
        return $this->hasMany('Modules\Oms\Models\RefundDetail', 'refund_id');
    }
}
