<?php

namespace Modules\Mall\Models;

use Backpack\CRUD\CrudTrait;

class SpecValue extends \BaseModel
{
    use CrudTrait;

    protected $table = 'mall_spec_value';
    protected $fillable = ['name', 'spec_id', 'store_id', 'sort_order'];

    public function spec()
    {
        return $this->belongsTo('Modules\Mall\Models\Spec', 'spec_id');
    }

    public function btnReturnSpec()
    {
        return '<a href="' .route('admin.mall.spec.index'). '" class="btn btn-default"><i class="fa fa-mail-reply"></i>  返回 规格列表</a>';
    }
}
