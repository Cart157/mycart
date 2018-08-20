<?php

namespace Modules\Oms\Models;

use Modules\Base\Models;
use Backpack\CRUD\CrudTrait;

class Consigner extends \BaseModel
{
    use CrudTrait;

    protected $table = 'oms_consigner';
    protected $fillable = ['name', 'province_code', 'city_code', 'area_code', 'area_info', 'address', 'mb_phone', 'tel_phone', 'zip_code', 'delivery_is_default', 'refund_is_default'];
}
