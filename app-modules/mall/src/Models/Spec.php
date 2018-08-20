<?php

namespace Modules\Mall\Models;

use Backpack\CRUD\CrudTrait;

class Spec extends \BaseModel
{
    use CrudTrait;
    
    protected $table = 'mall_spec';
    protected $fillable = ['name', 'sort_order'];

    public function values()
    {
        return $this->hasMany('Modules\Mall\Models\SpecValue', 'spec_id')
                    ->orderBy('sort_order', 'asc');
    }

    public function getValueCountHtml()
    {
        $url_index  = sprintf('%s?spec_id=%d', route('admin.mall.spec_value.index'), $this->id);
        $url_create = sprintf('%s?spec_id=%d', route('admin.mall.spec_value.create'), $this->id);
        return sprintf('<a href="%s">%d个</a> <a href="%s" class="btn btn-xs btn-default"><i class="fa fa-plus"></i> 添加规格值</a>', $url_index, $this->values->count(), $url_create);
    }

    public function btnManageSpecValue()
    {
        return '<a href="' .route('admin.mall.spec_value.index'). '" class="btn btn-default"><i class="fa fa-cog"></i> 管理规格值</a>';
    }
}
