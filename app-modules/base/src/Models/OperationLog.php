<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;
use Request;

class OperationLog extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_operation_log';
    protected $fillable = ['user_id', 'path', 'method', 'ip', 'input'];


    // 关系模型
    public function user()
    {
        return $this->belongsTo('Modules\Base\Models\User');
    }


    // 列值
    public function getOpMethod()
    {
        $method = [
            'POST'      => '新增',
            'PUT'       => '编辑',
            'DELETE'    => '删除',
        ];

        $cls = [
            'POST'      => 'label label-info',
            'PUT'       => 'label label-warning',
            'DELETE'    => 'label label-danger',
        ];

        return isset($method[$this->method]) ? sprintf('<span class="%s">%s</span>', $cls[$this->method], $method[$this->method]) : '';
    }

    public function getOpEntity()
    {
        $map = [
            'admin/product/setting'     => '产品库设置项',
            'admin/product/item-spu'    => '产品SPU',
            'admin/product/item-sku'    => '产品SKU',
            'admin/product/brand'       => '产品品牌',
            'admin/product/tech'        => '产品技术',
            'admin/product/type'        => '产品类型',
            'admin/product/item-correct'=> '产品纠错',
            'admin/product/unincluded-appr' => '产品未收录',
        ];

        $segments = explode('/', $this->path);
        $segments = array_values(array_filter($segments, function ($v) {
            return $v != '';
        }));
        $segments = array_slice($segments, 0, 3);

        $key = implode('/', $segments);

        return isset($map[$key]) ? $map[$key] : '';
    }
}
