<?php

namespace Modules\Oms\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderFlow extends \BaseModel
{
    use CrudTrait;
    use SoftDeletes;

    protected $table = 'oms_order_flow';
    protected $guarded = [];


    // 关系模型
    public function order()
    {
        return $this->belongsTo('Modules\Oms\Models\Order');
    }

    public function op_user()
    {
        return $this->belongsTo('Modules\Base\Models\User');
    }
}
