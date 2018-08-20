<?php

namespace Modules\Oms\Models;

use Backpack\CRUD\CrudTrait;

class Evaluation extends \BaseModel
{
    use CrudTrait;

    protected $table = 'mall_evaluation';

    // 关系模型
    public function user()
    {
        return $this->belongsTo('Modules\Base\Models\User', 'user_id')->select(['id', 'name', 'avatar'])
                    ->leftJoin('base_user_profile', 'base_user_profile.user_id', '=', 'base_user.id');
    }

    public function spu()
    {
        return $this->belongsTo('Modules\Mall\Models\GoodsSpu', 'spu_id');
    }

    public function order()
    {
        return $this->belongsTo('Modules\Mall\Oms\Order', 'order_id');
    }

    public function goods()
    {
        return $this->belongsTo('Modules\Mall\Models\Goods', 'goods_id');
    }
}
