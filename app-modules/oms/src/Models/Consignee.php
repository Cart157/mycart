<?php

namespace Modules\Oms\Models;

use Backpack\CRUD\CrudTrait;

class Consignee extends \BaseModel
{
    use CrudTrait;

    protected $table = 'oms_consignee';
    protected $fillable = ['name', 'parent_id', 'type_id', 'sort_order'];

    public function user()
    {
        return $this->belongsTo('Modules\Base\Models\User', 'user_id');
    }
}
