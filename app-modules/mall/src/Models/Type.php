<?php

namespace Modules\Mall\Models;

use Backpack\CRUD\CrudTrait;

class Type extends \BaseModel
{
    use CrudTrait;

    protected $table = 'mall_type';
    protected $fillable = ['name', 'sort_order'];

    public function specs()
    {
        return $this->belongsToMany('Modules\Mall\Models\Spec', 'mall_type_spec_mst', 'type_id', 'spec_id');
    }
}
