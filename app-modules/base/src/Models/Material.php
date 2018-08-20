<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class Material extends \BaseModel
{
    const LIMIT_PER_PAGE = 10;
    use CrudTrait;

    protected $table = 'base_user_material';
    protected $guarded = [];
    protected $hidden = ['updated_at','deleted_at'];


    public static function search($condition,$user_id,$field = ['*'])
    {
        // favorite
        // page
        // limit

        $q = self::select($field)->where('user_id',$user_id);

        if (isset($condition['type'])) {
            $q->where('type',$condition['type'] );
        } else {
            $q->where('type','image');
        }

        //模糊查找名称
        if(isset($condition['searchName']))
        {
            $q->where('name','like','%'.$condition['searchName'].'%');
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


        return $q->orderBy('created_at','desc')->get();

    }
}
