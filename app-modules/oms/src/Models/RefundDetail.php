<?php

namespace Modules\Oms\Models;

use Modules\Base\Models;
use Backpack\CRUD\CrudTrait;
use DB;
use JWTAuth;

class RefundDetail extends \BaseModel
{
    use CrudTrait;

    protected $table = 'oms_refund_detail';
    protected $fillable = ['refund_id', 'refund_type', 'option_type', 'option_value',
        'buyer_description', 'refund_logistics_company', 'refund_waybill_no',
        'seller_description', 'reject_reason', 'reject_image', 'consigner_refund_id'
    ];

    public function consigner()
    {
        return $this->belongsTo('Modules\Oms\Models\Consigner', 'consigner_refund_id');
    }
}
