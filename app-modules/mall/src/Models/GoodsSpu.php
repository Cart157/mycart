<?php

namespace Modules\Mall\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Modules\Product\Models as ProductModels;

class GoodsSpu extends \BaseModel
{
    use CrudTrait;

    const LIMIT_PER_PAGE = 10;

    protected $table = 'mall_goods_spu';
    protected $fillable = ['name', 'cover_image', 'category_id', 'category_id_1', 'category_id_2', 'category_id_3',
                            'type_id', 'brand_id', 'series_id', 'spu_spec', 'goods_price', 'market_price', 'official_price',
                            'freight', 'detail', 'is_sell', 'is_recommend', 'sort_order', 'store_id', 'seller_id', 'cloud_id'];


    // 关系属性
    public function category()
    {
        return $this->belongsTo('Modules\Mall\Models\Category', 'category_id');
    }

    public function type()
    {
        return $this->belongsTo('Modules\Mall\Models\Type', 'type_id');
    }

    public function brand()
    {
        return $this->belongsTo('Modules\Mall\Models\Brand', 'brand_id');
    }

    public function series()
    {
        return $this->belongsTo('Modules\Mall\Models\Brand', 'series_id');
    }

    public function sku()
    {
        return $this->hasMany('Modules\Mall\Models\Goods', 'spu_id');
    }

    public function service()
    {
        return $this->belongsToMany('Modules\Mall\Models\Service', 'mall_goods_service_mst', 'spu_id', 'service_id');
    }

    public function skuService($color_id)
    {
        return $this->belongsToMany('Modules\Mall\Models\Service', 'mall_goods_service_mst', 'spu_id', 'service_id')->wherePivot('color_id', $color_id);
    }

    public function default_sku()
    {
        $default_sku = $this->sku->where('is_default', 1)->first();
        return $default_sku ?: $this->sku->first();
    }

    public function is_recommend()
    {
        $exist = RecommendGoods::where('spu_id', $this->id)->first();
        return (boolean) $exist;
    }

    // crud column
    public function getSkuCountHtml()
    {
        $url_index  = sprintf('%s?spu_id=%d', route('admin.mall.goods.index'), $this->id);
        return sprintf('<a href="%s">%d款</a>', $url_index, $this->sku->count());
    }

    public function getCoverImageHtml()
    {
        return sprintf('<img src="%s" height="100">', $this->cover_image);
    }


    // 处理静态调用 search related
    public function scopeSearch(Builder $q, Array $condition, Array $fields = ['id', 'name', 'summary', 'cover_image', 'category_id'])
    {
        // brand_id
        // series_id
        // category_id
        // wd
        // limit
        // page
        $q->select($fields);

        if (isset($condition['brand_id'])) {
            $q->where('brand_id', $condition['brand_id']);
        }

        if (isset($condition['series_id'])) {
            $q->where('series_id', $condition['series_id']);
        }

        // if (isset($condition['category_id'])) {
        //     $category = Category::find($condition['category_id']);
        //     $all_category = $category->offspring()->pluck('id')->all();

        //     $q->whereIn('category_id', $all_category);
        // }

        if (isset($condition['wd'])) {
            $q->where('name', 'like', '%'.$condition['wd'].'%');
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

        return $q->orderBy('sort_order', 'asc')->get();
    }

    // 生成商品，一般用于定制（一次调用，多次调用）
    public static function syncCustomGoodsSpu($goods_info)
    {
        try {
            // // 要求goods_info
            // $goods_info = [
            //     // 'cloud_sku_id'  => [1, 2],   没用
            //     'custom_name'   => 'AJ1大红LV 商品图',
            //     'user_id'       => 123,
            //     'sync_diy'      => [
            //         // size_id
            //         '115'   => [
            //             'stock' =>  5,
            //             'price' =>  11,
            //         ],
            //     ],
            //     'sync_stocks'   => [
            //         // size_id
            //         '55'    => [
            //             'stock' =>  5,
            //             'price' =>  11,
            //         ],
            //         '56'    => [
            //             'stock' =>  6,
            //             'price' =>  12,
            //         ],
            //         '57'    => [
            //             'stock' =>  7,
            //             'price' =>  13,
            //         ],
            //     ],
            //     'sync_futures'  => [
            //         // size_id
            //         '55'    => [
            //             'stock' =>  5,
            //             'price' =>  11,
            //         ],
            //         '56'    => [
            //             'stock' =>  6,
            //             'price' =>  12,
            //         ],
            //         '57'    => [
            //             'stock' =>  7,
            //             'price' =>  13,
            //         ],
            //         '58'    => [
            //             'stock' =>  7,
            //             'price' =>  13,
            //         ],
            //         '59'    => [
            //             'stock' =>  7,
            //             'price' =>  13,
            //         ],
            //     ],
            //     'images'        => [
            //         'https://static.tosneaker.com/uploads/_ueditor/image/20171122/1511324583550680.jpg',// 主图
            //         'https://static.tosneaker.com/uploads/_ueditor/image/20171122/1511324583550680.jpg',// 副图
            //         'https://static.tosneaker.com/uploads/_ueditor/image/20171122/1511324583550680.jpg',// 效果图
            //         'https://static.tosneaker.com/uploads/_ueditor/image/20171122/1511324583550680.jpg',// 效果图
            //         'https://static.tosneaker.com/uploads/_ueditor/image/20171122/1511324583550680.jpg',// 效果图
            //         'https://static.tosneaker.com/uploads/_ueditor/image/20171122/1511324583550680.jpg',// 效果图
            //     ],
            //     'detail'        => "文案描述\n文案描述",
            //     'cloud_id'      => 100,
            // ];

            // TODO:验证一下这个 $goods_info

            $detail = '';
            if (isset($goods_info['detail'])) {
                $detail = '<p class="pure-text">' . nl2br($goods_info['detail'], false) . '</p>';
            }

            // 把七牛的图片移除域名
            foreach ($goods_info['images'] as $idx => $url) {
                if (!starts_with($url, '/uploads/custom/post')) {
                    abort(403, '上传图片的地址格式有误');
                }

                $detail .= '<p><img src="' . $goods_info['images'][$idx] . '"></p>';
            }

            // // 产品库云ID（暂定）
            // $cloud_sku_id = 0;

            // 品牌和系列全部为0（暂定）
            $brand_id      = 0;
            $series_id     = 0;

            // 分类也全部为0（暂定）
            $category_id   = 0;
            $category_id_1 = 0;
            $category_id_2 = 0;
            $category_id_3 = 0;

            // 二、创建spu
            $sync_diy_color = SpecValue::firstOrCreate(['name' => '自己提供原鞋', 'spec_id' => 1, 'store_id' => 1]);
            $sync_stocks_color = SpecValue::firstOrCreate(['name' => '成品现货', 'spec_id' => 1, 'store_id' => 1]);
            $sync_futures_color = SpecValue::firstOrCreate(['name' => '成品预售', 'spec_id' => 1, 'store_id' => 1]);

            $color_ids = $size_ids = [];
            if (isset($goods_info['sync_diy'])) {
                $color_ids[] = $color_id = $sync_diy_color->id;

                $sku_info_diy = [];
                foreach ($goods_info['sync_diy'] as $key => $value) {
                    if (!in_array($key, $size_ids)) {
                        $size_ids[] = $key;
                    }

                    $color_size_id = $color_id .'_'. $key;
                    $value['cloud_id'] = $goods_info['cloud_id'];
                    $sku_info_diy[$color_size_id] = $value;
                }
            }

            if (isset($goods_info['sync_stocks'])) {
                $color_ids[] = $color_id = $sync_stocks_color->id;

                $sku_info_stocks = [];
                foreach ($goods_info['sync_stocks'] as $key => $value) {
                    if (!in_array($key, $size_ids)) {
                        $size_ids[] = $key;
                    }

                    $color_size_id = $color_id .'_'. $key;
                    $value['cloud_id'] = $goods_info['cloud_id'];
                    $sku_info_stocks[$color_size_id] = $value;
                }
            }

            if (isset($goods_info['sync_futures'])) {
                $color_ids[] = $color_id = $sync_futures_color->id;

                $sku_info_futures = [];
                foreach ($goods_info['sync_futures'] as $key => $value) {
                    if (!in_array($key, $size_ids)) {
                        $size_ids[] = $key;
                    }

                    $color_size_id = $color_id .'_'. $key;
                    $value['cloud_id'] = $goods_info['cloud_id'];
                    $sku_info_futures[$color_size_id] = $value;
                }
            }

            $spu_spec  = self::makeSpec($color_ids, $size_ids);
            $cloud_sku = ProductModels\ItemSku::find($goods_info['cloud_id']);
            if (!$cloud_sku || !$cloud_sku->spu) {
                abort(403, '先择的定制原鞋在产品库中不存在');
            }

            $spu = self::create([
                'name'          => $goods_info['custom_name'],
                'cover_image'   => $goods_info['images'][0],
                'category_id'   => $category_id,
                'category_id_1' => $category_id_1,
                'category_id_2' => $category_id_2,
                'category_id_3' => $category_id_3,
                'type_id'       => 0,
                'brand_id'      => $brand_id,
                'series_id'     => $series_id,
                'spu_spec'      => json_encode($spu_spec, JSON_UNESCAPED_UNICODE),
                'goods_price'   => 0,
                'detail'        => $detail,
                'is_sell'       => 1,
                'is_recommend'  => 0,
                'is_lock'       => 0,
                'store_id'      => 1,
                'seller_id'     => $goods_info['user_id'],
                'cloud_id'      => $cloud_sku->spu->id,
                'sort_order'    => 0,
            ]);

            // 创建Image、Sku
            if (isset($goods_info['sync_diy'])) {
                self::syncCustomGoodsSku($spu, $sku_info_diy, 'sync_diy');
            }

            if (isset($goods_info['sync_stocks'])) {
                self::syncCustomGoodsSku($spu, $sku_info_stocks, 'sync_stocks');
            }

            if (isset($goods_info['sync_futures'])) {
                self::syncCustomGoodsSku($spu, $sku_info_futures, 'sync_futures');
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $spu->id;
    }

    public static function syncCustomGoodsSku($spu, $sku_info, $sync_type)
    {
        // 五、不同配色：创建SKU
        $idx= 1;
        foreach ($sku_info as $color_size_id => $item) {
            list($color_id, $size_id) = explode('_', $color_size_id);
            $sku_spec = self::makeSpec($color_id, $size_id);

            $sync_type_title = [
                'sync_diy'      =>  ' [定制 - 自己提供原鞋]',
                'sync_stocks'   =>  ' [定制 - 成品现货]',
                'sync_futures'  =>  ' [定制 - 成品预售]',
            ];

            $sku = Goods::create([
                'name'          => $spu->name . $sync_type_title[$sync_type],
                'spu_id'        => $spu->id,
                'item_no'       => '',
                'cover_image'   => $spu->cover_image,
                'seller_id'     => $spu->seller_id,
                'category_id'   => $spu->category_id,
                'category_id_1' => $spu->category_id_1,
                'category_id_2' => $spu->category_id_2,
                'category_id_3' => $spu->category_id_3,
                'type_id'       => $spu->type_id,
                'brand_id'      => $spu->brand_id,
                'series_id'     => $spu->series_id,
                'sku_spec'      => json_encode($sku_spec, JSON_UNESCAPED_UNICODE),
                'color_id'      => $color_id,
                'goods_price'   => $item['price'],
                'freight'       => 0,   // 定制商品默认一次仅能拍一件，所以包邮
                'stock'         => $item['stock'],
                'detail'        => $spu->detail,
                'is_sell'       => $spu->is_sell,
                'sell_time'     => null,
                'cloud_id'      => $item['cloud_id'],
                'service_type'  => $sync_type == 'sync_diy' ? 21 : ($sync_type == 'sync_stocks' ? 20 : 23),
            ]);

            if ($idx == 1) {
                $goods_image = GoodsImage::create([
                    'spu_id'        => $spu->id,
                    'color_id'      => $color_id,
                    'path'          => $spu->cover_image,
                    'sort_order'    => 0,
                ]);
            }

            $idx++;
        }
    }

    // 生成商品，一般用于定制（一次调用，多次调用）
    public static function getCustomGoodsSku($goods_spu_id)
    {
        $goods_spu = GoodsSpu::find($goods_spu_id);
        if (!$goods_spu) {
            return [];
        }

        // 整理商品版本信息
        $goods_size = [];
        foreach ($goods_spu->sku as $sku) {
            $sku_spec = json_decode($sku->sku_spec, true);
            // $goods_size[$sku->color_id][$sku_spec[1]['value']] = array_only($sku->toArray(), ['stock', 'goods_price']);

            $stock_info = array_only($sku->toArray(), ['stock', 'goods_price']);
            $stock_info['size_id'] = $sku_spec[1]['value'];
            $stock_info['sku_id']  = $sku->id;

            if ($sku->color->name == '自己提供原鞋') {
                $color_key = 'sync_diy';
            } elseif ($sku->color->name == '成品现货') {
                $color_key = 'sync_stocks';
            } elseif ($sku->color->name == '成品预售') {
                $color_key = 'sync_futures';
            }

            $goods_size[$color_key][] = $stock_info;
        }

        return $goods_size;
    }

    // 生成商品，一般用于定制（一次调用，多次调用）
    public static function editCustomGoodsSku($goods_info, $goods_spu_id)
    {
        try {
            // // 要求goods_info
            // $goods_info = [
            //     'custom_name'   => 'AJ1大红LV 商品图',
            //     'user_id'       => 123,
            //     'sync_diy'      => [
            //         // size_id
            //         '115'   => [
            //             'stock' =>  5,
            //             'price' =>  11,
            //         ],
            //     ],
            //     'sync_stocks'   => [
            //         // size_id
            //         '55'    => [
            //             'stock' =>  5,
            //             'price' =>  11,
            //         ],
            //         '56'    => [
            //             'stock' =>  6,
            //             'price' =>  12,
            //         ],
            //         '57'    => [
            //             'stock' =>  7,
            //             'price' =>  13,
            //         ],
            //     ],
            //     'sync_futures'  => [
            //         // size_id
            //         '55'    => [
            //             'stock' =>  5,
            //             'price' =>  11,
            //         ],
            //         '56'    => [
            //             'stock' =>  6,
            //             'price' =>  12,
            //         ],
            //         '57'    => [
            //             'stock' =>  7,
            //             'price' =>  13,
            //         ],
            //         '58'    => [
            //             'stock' =>  7,
            //             'price' =>  13,
            //         ],
            //         '59'    => [
            //             'stock' =>  7,
            //             'price' =>  13,
            //         ],
            //     ],
            //     'images'        => [
            //         'https://static.tosneaker.com/uploads/_ueditor/image/20171122/1511324583550680.jpg',// 主图
            //         'https://static.tosneaker.com/uploads/_ueditor/image/20171122/1511324583550680.jpg',// 副图
            //         'https://static.tosneaker.com/uploads/_ueditor/image/20171122/1511324583550680.jpg',// 效果图
            //         'https://static.tosneaker.com/uploads/_ueditor/image/20171122/1511324583550680.jpg',// 效果图
            //         'https://static.tosneaker.com/uploads/_ueditor/image/20171122/1511324583550680.jpg',// 效果图
            //         'https://static.tosneaker.com/uploads/_ueditor/image/20171122/1511324583550680.jpg',// 效果图
            //     ],
            //     'detail'        => "文案描述\n文案描述",
            // ];

            // 取出原商品和他的spu
            $goods_spu = Models\GoodsSpu::find($goods_spu_id);
            if (!$goods_spu) {
                abort(403, '要更新的同步商品不存在');
            }

            $default_sku = $goods_spu->default_sku();
            if (!$goods_spu) {
                abort(403, '要更新的同步商品异常');
            }

            // 更新spu

            $sync_diy_color = SpecValue::firstOrCreate(['name' => '自己提供原鞋', 'spec_id' => 1, 'store_id' => 1]);
            $sync_stocks_color = SpecValue::firstOrCreate(['name' => '成品现货', 'spec_id' => 1, 'store_id' => 1]);
            $sync_futures_color = SpecValue::firstOrCreate(['name' => '成品预售', 'spec_id' => 1, 'store_id' => 1]);

            $all_color = [];
            $all_size  = [];
            if (isset($goods_info['sync_diy'])) {
                $all_color[] = $color_id = $sync_diy_color->id;

                $sku_info_diy = [];
                foreach ($goods_info['sync_diy'] as $key => $value) {
                    if (!in_array($key, $all_size)) {
                        $all_size[] = $key;
                    }

                    $color_size_id = $color_id .'_'. $key;
                    $value['cloud_id'] = $default_sku->cloud_id;
                    $sku_info_diy[$color_size_id] = $value;
                }
            }

            if (isset($goods_info['sync_stocks'])) {
                $all_color[] = $color_id = $sync_stocks_color->id;

                $sku_info_stocks = [];
                foreach ($goods_info['sync_stocks'] as $key => $value) {
                    if (!in_array($key, $all_size)) {
                        $all_size[] = $key;
                    }

                    $color_size_id = $color_id .'_'. $key;
                    $value['cloud_id'] = $default_sku->cloud_id;
                    $sku_info_stocks[$color_size_id] = $value;
                }
            }

            if (isset($goods_info['sync_futures'])) {
                $all_color[] = $color_id = $sync_futures_color->id;

                $sku_info_futures = [];
                foreach ($goods_info['sync_futures'] as $key => $value) {
                    if (!in_array($key, $all_size)) {
                        $all_size[] = $key;
                    }

                    $color_size_id = $color_id .'_'. $key;
                    $value['cloud_id'] = $default_sku->cloud_id;
                    $sku_info_futures[$color_size_id] = $value;
                }
            }

            // 再取商品的sku
            $goods_color_arr = $goods_spu->sku->each(function($item) {
                $item->color_name = $item->color->name;
            })->pluck('color_name', 'color_id')->all();

            $old_color = array_keys($goods_color_arr);

            $new_color = array_diff($all_color, $old_color);
            $del_color = array_diff($old_color, $all_color);
            $chg_color = array_intersect($all_color, $old_color);

            if ($del_color) {
                // TODO:如果有人买了删除的商品怎么办
                $goods_spu->sku()->where('spu_id', $goods_spu_id)
                                 ->whereIn('color_id', $del_color)
                                 ->forceDelete();
            }

            // 把七牛的图片移除域名
            foreach ($goods_info['images'] as $idx => $url) {
                if (starts_with($url, 'https://static.tosneaker.com/')) {
                    $goods_info['images'][$idx] = '/'. get_qiniu_key($url);
                }
            }

            $spu_spec = self::makeSpec($all_color, $all_size);

            $spu = self::updateOrCreate(
                ['id' => $goods_spu_id],
                [
                    'name'          => $goods_info['custom_name'],
                    'cover_image'   => $goods_info['images'][0],
                    'spu_spec'      => json_encode($spu_spec, JSON_UNESCAPED_UNICODE),
                    'detail'        => '<p>' . nl2br($goods_info['detail'], false) . '</p>',
                ]
            );

            $spu_spec = self::makeSpec($color_ids, $size_ids);

            if ($new_color) {
                foreach ($new_color as $color_id) {
                    if ($color_id == $sync_diy_color->id) {
                        self::syncCustomGoodsSku($spu, $sku_info_diy, 'sync_diy');
                    } elseif ($color_id == $sync_stocks_color->id) {
                        self::syncCustomGoodsSku($spu, $sku_info_stocks, 'sync_stocks');
                    } elseif ($color_id == $sync_futures_color->id) {
                        self::syncCustomGoodsSku($spu, $sku_info_futures, 'sync_futures');
                    }
                }
            }

            // 原本有的，更新值
            if ($chg_color) {
                foreach ($chg_color as $color_id) {
                    if ($color_id == $sync_diy_color->id) {
                        self::chgCustomGoodsSku($spu, $sku_info_diy, 'sync_diy');
                    } elseif ($color_id == $sync_stocks_color->id) {
                        self::chgCustomGoodsSku($spu, $sku_info_stocks, 'sync_stocks');
                    } elseif ($color_id == $sync_futures_color->id) {
                        self::chgCustomGoodsSku($spu, $sku_info_futures, 'sync_futures');
                    }
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function chgCustomGoodsSku($spu, $sku_info, $sync_type)
    {
        $sku_specs_json = [];
        foreach ($sku_info as $color_size_id => $item) {
            list($color_id, $size_id) = explode('_', $color_size_id);
            $sku_specs_json[$size_id] = json_encode($sku_spec, JSON_UNESCAPED_UNICODE);

            // 更新或创建 spu
            $sku = Goods::updateOrCreate(
                [
                    'spu_id'    => $spu->id,
                    'sku_spec'  => $sku_specs_json[$size_id],
                ],
                [
                    'name'          => $spu->name . $sync_type_title[$sync_type],
                    'item_no'       => '',
                    'cover_image'   => $spu->cover_image,
                    'seller_id'     => $spu->seller_id,
                    'category_id'   => $spu->category_id,
                    'category_id_1' => $spu->category_id_1,
                    'category_id_2' => $spu->category_id_2,
                    'category_id_3' => $spu->category_id_3,
                    'type_id'       => $spu->type_id,
                    'brand_id'      => $spu->brand_id,
                    'series_id'     => $spu->series_id,
                    'color_id'      => $color_id,
                    'goods_price'   => $item['price'],
                    'freight'       => 0,   // 定制商品默认一次仅能拍一件，所以包邮
                    'stock'         => $item['stock'],
                    'detail'        => $spu->detail,
                    'is_sell'       => $spu->is_sell,
                    'sell_time'     => null,
                    'cloud_id'      => $item['cloud_id'],
                    'service_type'  => $sync_type == 'sync_diy' ? 21 : ($sync_type == 'sync_stocks' ? 20 : 23),
                ]
            );

            // 更新或新建各颜色首图
            $goods_image = GoodsImage::updateOrCreate(
                [
                    'spu_id'        => $spu->id,
                    'color_id'      => $color_id,
                ],
                [
                    'path'          => $spu->cover_image,
                    'sort_order'    => 0,
                ]
            );
        }

        // 删除老鞋码（color_id为防止误删其他颜色的sku）
        $spu->sku()->where('color_id', $color_id)->whereNotIn('sku_spec', $sku_specs_json)->forceDelete();
    }

    public static function makeSpec($color_ids, $size_ids)
    {
        // spu 的场合进行排序
        if (is_array($color_ids)) {
            sort($color_ids);
        }

        if (is_array($size_ids)) {
            sort($size_ids);
        }

        $spec = [
            [
                'spec_id'   => 1,
                'spec_name' => "颜色",
                'value'     => $color_ids,
            ],
            [
                'spec_id'   => 2,
                'spec_name' => "鞋码",
                'value'     => $size_ids,
            ]
        ];

        return $spec;
    }
/*
    public function related($condition)
    {
        //spokesman_id
        //tech_id
        //limit
        //page
        $q = $this->select('id', 'name', 'official_price', 'cover_image');

        if (isset($condition['spokesman_id'])) {
            $q->where('spokesman_id', $condition['spokesman_id']);
        }

        if (isset($condition['tech_id'])) {
            $q->where('item_tech', 'like', '%v_'.$condition['tech_id'].'_v%');
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

        return $q->orderBy('sort_order', 'asc')->get();
    }
*/
/*
    public function setCoverImageAttribute($value)
    {
        $attribute_name = "cover_image";
        $disk = "uploads";
        $destination_path = "mall/goods/".$this->id;

        $this->uploadFileToDisk($value, $attribute_name, $disk, $destination_path);
        $this->attributes[$attribute_name] = sprintf('/%s/%s', $disk, $this->attributes[$attribute_name]);
    }
 */
}
