<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class Tag extends \BaseModel
{
    use CrudTrait;

    const LIMIT_PER_PAGE = 10;

    protected $table = 'base_tag';
    protected $fillable = ['name', 'item_no', 'cover_image'];

    public function sku()
    {
        return $this->belongsTo('Modules\Product\Models\ItemSku', 'id', 'id');
    }

    public static function search($condition, $fields = ['id', 'name', 'item_no', 'cover_image'])
    {
        // wd
        // limit
        // page
        $q = self::select($fields);

        if (isset($condition['wd'])) {
            $q->whereRaw("CONCAT(IFNULL(name, ''), IFNULL(item_no, '')) like ?", ['%'.$condition['wd'].'%']);
        }

        $take_num = self::LIMIT_PER_PAGE;
        if (isset($condition['limit'])) {
            $take_num = (int) $condition['limit'];
            $q->take($take_num);
        }

        if (isset($condition['page'])) {
            $skip_num = $take_num * ($condition['page'] - 1);
            $q->skip($skip_num)
              ->take($take_num);
        }

        return $q->get();
    }
}
