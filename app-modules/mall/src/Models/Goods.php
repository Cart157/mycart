<?php

namespace Modules\Mall\Models;

use Backpack\CRUD\CrudTrait;
use DB;

class Goods extends \BaseModel
{
    use CrudTrait;

    const LIMIT_PER_PAGE = 10;

    protected $table = 'mall_goods';
    protected $fillable = ['name', 'spu_id', 'item_no', 'cover_image', 'seller_id', 'service_type', 'service_type_custom',
                            'category_id', 'category_id_1', 'category_id_2', 'category_id_3',
                            'type_id', 'brand_id', 'series_id', 'sku_spec', 'sku_attr', 'color_id', 'goods_price', 'market_price', 'official_price',
                            'freight', 'stock', 'detail', 'is_sell', 'sell_time', 'sort_order', 'cloud_id'];

    protected $hidden = [
        'discount', 'total_sales_cnt'
    ];

    // 关系属性
    public function seller()
    {
        return $this->belongsTo('Modules\Base\Models\User', 'seller_id');
    }

    public function spu()
    {
        return $this->belongsTo('Modules\Mall\Models\GoodsSpu', 'spu_id');
    }

    public function images()
    {
        return $this->spu->hasMany('Modules\Mall\Models\GoodsImage', 'spu_id')->where('color_id', $this->color_id)->orderBy('sort_order', 'asc');
    }

    public function cloud_images()
    {
        return $this->hasMany('Modules\Product\Models\ItemImage', 'sku_id', 'cloud_id');
    }

    public function color()
    {
        return $this->belongsTo('Modules\Mall\Models\SpecValue', 'color_id')->select(['name']);
    }

    public function topic()
    {
        return $this->belongsToMany('Modules\Mall\Models\Topic', 'mall_topic_goods_mst', 'goods_id', 'topic_id');
    }

    public function btnReturnGoodsSpu()
    {
        return '<a href="' . route('admin.mall.goods_spu.index') . '" class="btn btn-default"><i class="fa fa-mail-reply"></i> 返回 商品列表</a>';
    }

    public static function search($condition, $fields = ['id', 'name', 'item_no', 'cover_image', 'spu_id', 'color_id'])
    {

        // category_id、topic_id
        // wd

        // price_from
        // price_to
        // sort_by

        // brand_id
        // series_id

        // limit
        // page
        $q = self::select($fields)->where('is_sell', 1);

        // 检索时进行合并
        if (isset($condition['category_id'])) {
            $q->where('category_id', $condition['category_id']);
        } elseif (isset($condition['topic_id'])) {
            // 获取那些goods_id属于（topic_id是$condition['topic_id']的关系条目）的Goods
            $q->whereHas('topic', function ($query) use($condition) {
                $query->where('topic_id', $condition['topic_id']);
            });
        }

        if (isset($condition['wd'])) {
            $q->where('name', 'like', '%'.$condition['wd'].'%');
        }

        if (isset($condition['price_from'])) {
            $price_from = (int) $condition['price_from'];
            $q->where('goods_price', '>=', $price_from);
        }

        if (isset($condition['price_to'])) {
            $price_to   = (int) $condition['price_to'];
            $q->where('goods_price', '<=', $price_to);
        }

        if (isset($condition['brand_id'])) {
            $q->where('brand_id', $condition['brand_id']);
        }

        if (isset($condition['series_id'])) {
            $q->where('series_id', $condition['series_id']);
        }

        if (isset($condition['sort_by'])) {
            // 销量排序（同色合并销量）
            if ($condition['sort_by'] == 'sales_cnt') {
                $q->addSelect(DB::raw('sum(sales_cnt) as total_sales_cnt'));
                $q->orderBy('sales_cnt', 'desc');
            // 折扣排序（售价比上官价的折扣率）
            } elseif ($condition['sort_by'] == 'discount') {
                $q->addSelect(DB::raw('(goods_price/official_price) as discount'));
                $q->orderBy('discount', 'asc');
            // 价格从高到低排序
            } elseif ($condition['sort_by'] == 'price_desc') {
                $q->orderBy(DB::raw('max(goods_price)'), 'desc');
            // 价格从低到高排序
            } elseif ($condition['sort_by'] == 'price_asc') {
                $q->orderBy(DB::raw('min(goods_price)'), 'asc');
            // 创建时间倒序
            } elseif ($condition['sort_by'] == 'add_desc') {
                $q->orderBy('created_at', 'desc');
            }
        }

        $q->groupBy('spu_id','color_id');

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


    // CRUD
    public function getCoverImageHtml()
    {
        return sprintf('<img src="%s" height="100">', $this->cover_image);
    }
}
