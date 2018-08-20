<?php

namespace Modules\Mall\Controllers\Store;

use Modules\Mall\Models;
use Modules\Product\Models as PModels;
use Request;
use DB;
use Validator;
use Illuminate\Validation\Rule;
use Illuminate\Filesystem\Filesystem;

class GoodsController extends \BaseController
{
/*
    public function index()
    {
        $q = Models\GoodsSpu::where('mall_goods_spu.is_sell', 1);

        if (Request::input('goods_name')) {
            $q->whereHas('sku', function ($query) {
                $query->where('name', 'like', '%'.Request::input('goods_name').'%');
            });
        }

        if (Request::input('item_no')) {
            $q->whereHas('sku', function ($query) {
                $query->where('item_no', Request::input('goods_name'));
            });
        }

        if (Request::input('price_from')) {
            $q->whereHas('sku', function ($query) {
                $query->where('goods_price', '>=', (int) Request::input('price_from'));
            });
        }

        if (Request::input('price_to')) {
            $q->whereHas('sku', function ($query) {
                $query->where('goods_price', '<=', (int) Request::input('price_to'));
            });
        }

        if (in_array(Request::input('order_by'), ['total_stock', 'sales_cnt', 'sell_time'])) {
            $order_by = Request::input('order_by');
        } else {
            $order_by = 'sell_time';
        }

        if (in_array(Request::input('order_mod'), ['asc', 'desc'])) {
            $order_mod = Request::input('order_mod');
        } else {
            $order_mod = 'desc';
        }

        $q->join('mall_goods', 'mall_goods_spu.id', '=', 'mall_goods.spu_id')->groupBy('mall_goods_spu.id');

        $ret = $q->select(DB::raw('mall_goods_spu.*, sum(mall_goods.stock) as total_stock, sum(mall_goods.sales_cnt) as total_sales_cnt'))
                 ->orderBy($order_by, $order_mod)
                 ->paginate(15);

        $result['data'] = $ret;

        return view('mall::store.goods.index', $result);
    }
 */

    public function index()
    {
        $q = Models\Goods::where('is_sell', 1);

        if (Request::input('goods_name')) {
            $q->where('name', 'like', '%'.Request::input('goods_name').'%');
        }

        if (Request::input('item_no')) {
            $q->where('item_no', 'like', '%'.Request::input('item_no').'%');
        }

        if (Request::input('price_from')) {
            $q->where('goods_price', '>=', (int) Request::input('price_from'));
        }

        if (Request::input('price_to')) {
            $q->where('goods_price', '<=', (int) Request::input('price_to'));
        }

        if (in_array(Request::input('order_by'), ['total_stock', 'sales_cnt', 'sell_time'])) {
            $order_by = Request::input('order_by');
        } else {
            $order_by = 'sell_time';
        }

        if (in_array(Request::input('order_mod'), ['asc', 'desc'])) {
            $order_mod = Request::input('order_mod');
        } else {
            $order_mod = 'desc';
        }

        $result['data'] = $q->select(DB::raw('mall_goods.*, sum(stock) as total_stock, sum(sales_cnt) as total_sales_cnt'))
                            ->groupBy('spu_id', 'color_id')
                            ->orderBy($order_by, $order_mod)
                            ->paginate(15);

        return view('mall::store.goods.index', $result);
    }
/*
    public function batchUpdate()
    {
        if (empty(Request::input('id'))) {
            $res['result']  = false;
            $res['message'] = '没有选中的商品';

            return $res;
        }

        $exist = Models\GoodsSpu::whereIn('id', Request::input('id'))
                                ->where('is_lock', '>', 0)
                                ->first();
        if ($exist) {
            $res['result']  = false;
            $res['message'] = '选中的包含橱窗商品，不能下架';
        } else {
            Models\GoodsSpu::whereIn('id', Request::input('id'))
                           ->update(['is_sell' => 0]);

            $res['result']  = true;
            $res['message'] = '下架成功';
        }

        return $res;
    }
 */
    public function batchUpdate()
    {
        if (empty(Request::input('id'))) {
            $res['result']  = false;
            $res['message'] = '没有选中的商品';

            return $res;
        }

        foreach (Request::input('id') as $sku_id) {
            $sku = Models\Goods::find($sku_id);
            $same_color_ids = $sku->spu->sku()->where('color_id', $sku->color_id)->get()->pluck('id')->toArray();

            Models\Goods::whereIn('id', $same_color_ids)->update(['is_sell' => 0]);
        }

        $res['result']  = true;
        $res['message'] = '下架成功';

        return $res;
    }
/*
    public function batchDelete()
    {
        if (empty(Request::input('id'))) {
            $res['result']  = false;
            $res['message'] = '没有选中的商品';

            return $res;
        }

        $exist = Models\GoodsSpu::whereIn('id', Request::input('id'))
                                ->where('is_lock', '>', 0)
                                ->first();
        if ($exist) {
            $res['result']  = false;
            $res['message'] = '选中的包含橱窗商品，不能删除';
        } else {
            Models\GoodsSpu::destroy(Request::input('id'));

            $res['result']  = true;
            $res['message'] = '删除成功';
        }

        return $res;
    }
 */
    public function batchDelete()
    {
        if (empty(Request::input('id'))) {
            $res['result']  = false;
            $res['message'] = '没有选中的商品';

            return $res;
        }

        foreach (Request::input('id') as $sku_id) {
            $sku = Models\Goods::find($sku_id);
            $same_color_ids = $sku->spu->sku()->where('color_id', $sku->color_id)->get()->pluck('id')->toArray();

            Models\Goods::destroy($same_color_ids);
        }

        $res['result']  = true;
        $res['message'] = '删除成功';

        return $res;
    }

    public function create()
    {
        $value = Models\SpecValue::where('spec_id', 2)->orderBy('name')->get()->pluck('name', 'id')->toArray();
        $result['data'] = $value;

        return view('mall::store.goods.publish', $result);
    }

    public function store()
    {
        //dd(Request::all());

        //验证条件
        $validator = Validator::make(Request::all(), [
            'cloud_id'                  => 'required|integer',
            'category_id_1'             => 'required|integer',
            'category_id_2'             => 'required|integer',
            'category_id_3'             => 'required|integer',
            'goods_info'                => 'required|array',
            'goods_info.*'              => 'required|array',
            'goods_info.*.color_name'   => 'required',
            'goods_info.*.goods_title'  => 'required',
            'goods_info.*.freight'      => 'nullable|numeric',
            'goods_info.*.has_freight'  => [
                'required',
                Rule::in([0, 1]),
            ],
            'goods_info.*.is_sell'      => [
                'required',
                Rule::in([0, 1]),
            ],
            'goods_info.*.sell_time'                => 'nullable|date',
            'goods_info.*.goods_detail'             => 'required',
            'goods_info.*.service'                  => 'array',
            'goods_info.*.service.*'                => 'integer',
            'goods_info.*.goods_size'               => 'required|array',
            'goods_info.*.goods_size.*'             => 'array',
            'goods_info.*.goods_size.*.stock'       => 'required|integer',
            'goods_info.*.goods_size.*.goods_price' => 'required|numeric',
            'goods_info.*.goods_image'              => 'required|array',
        ]);

        if ($validator->fails()) {
            return [
                'result'  => false,
                'message' => $validator->errors()->first(),
            ];
        }

        // 一、设置Brand、Series，不存在则新建
        $req = Request::all();
        $cloud_spu = PModels\ItemSpu::findOrFail($req['cloud_id']);
        $cloud_brand = $cloud_spu->brand;
        $cloud_series = $cloud_spu->series;
            //1、brand
        $brand = Models\Brand::updateOrCreate([
            'cloud_brand_id'    => $cloud_brand->id,
        ],[
            'name'              => $cloud_brand->name,
            'parent_id'         => 0,
            'type'              => 0,
            'initial'           => $cloud_brand->initial,
            'image'             => $cloud_brand->image,
        ]);
            //2、series
        $series = Models\Brand::updateOrCreate([
            'cloud_brand_id'    => $cloud_series->id,
        ],[
            'name'              => $cloud_series->name,
            'parent_id'         => $brand->id,
            'type'              => 1,
        ]);

        // 二、创建spu
        $color_ids = $size_ids = [];
        foreach ($req['goods_info'] as $key => $value) {
            // 3、创建SpecValue
            $spec_value = Models\SpecValue::create(['name' => $value['color_name'], 'spec_id' => 1, 'store_id' => 1]);
            $color_ids[] = $spec_value->id;
            $sku_color_ids[$key] = $spec_value->id;

            foreach ($value['goods_size'] as $key => $value) {
                if (!in_array($key, $size_ids)) {
                    $size_ids[] = $key;
                }
            }
        }
        // 创建spu_spec
        $spu_spec = $this->makeSpec($color_ids, $size_ids);

        // 4、创建spu
        // $cloud_spu = PModels\ItemSpu::findOrFail($req['cloud_id']); // 上面取过了
        $spu = Models\GoodsSpu::create([
            'name'          => $cloud_spu->name,
            'category_id'   => $req['category_id_3'],
            'category_id_1' => $req['category_id_1'],
            'category_id_2' => $req['category_id_2'],
            'category_id_3' => $req['category_id_3'],
            'type_id'       => 1,
            'brand_id'      => $brand->id,
            'series_id'     => $series->id,
            'spu_spec'      => json_encode($spu_spec, JSON_UNESCAPED_UNICODE),
            'goods_price'   => 0,
            'is_sell'       => 1,
            'is_recommend'  => 0,
            'is_lock'       => 0,
            'store_id'      => 1,
            'cloud_id'      => $req['cloud_id'],
            'sort_order'    => 0,
        ]);

        // 创建Service、Image、Sku
        foreach ($req['goods_info'] as $cloud_sku_id => $same_value) {
            $item_no = PModels\ItemSku::find($cloud_sku_id)->item_no;
            $color_id = $sku_color_ids[$cloud_sku_id];

            // 三、同一配色：创建Service
                //5、mall_goods_service_mst
            if (isset($same_value['service'])) {
                $spu->service()->attach($same_value['service'], ['color_id' => $color_id]);
            }

            // 四、同一配色：保存图片
                //设置存储图片目标路径
            $filesystem = new Filesystem();
            $target_dir = public_path().sprintf('/uploads/mall/goods/%d/%d', $spu->id, $color_id);

            //存储图片&写数据库
            foreach ($same_value['goods_image'] as $key => $path) {
                //存储图片目标路径不存在则新建文件夹
                if (!$filesystem->exists($target_dir)) {
                    $filesystem->makeDirectory($target_dir, 0755 ,true);
                }

                // 移动、复制图片
                $target_filename = basename($path);
                $target_path = sprintf('/uploads/mall/goods/%d/%d/%s', $spu->id, $color_id, $target_filename);
                if (starts_with($path, '/uploads/_tmp')) {
                    $filesystem->move(public_path().$path, public_path().$target_path);
                } else {
                    $filesystem->copy(public_path().$path, public_path().$target_path);
                }

                // 6、保存到数据库 mall_goods_image
                $create_images = Models\GoodsImage::create([
                    'spu_id'        => $spu->id,
                    'color_id'      => $color_id,
                    'path'          => $target_path,
                    'sort_order'    => $key,
                ]);

                //如果是第一张图片，设该路径为$cover_image
                if ($key == 0) {
                    $cover_image = $target_path;

                }
            }

            // 五、不同配色：创建SKU
                //7、SKU
            foreach ($same_value['goods_size'] as $size_id => $diff_value) {
                $sku_spec = $this->makeSpec($color_id, $size_id);
                $sku = Models\Goods::create([
                    'name'          => $same_value['goods_title'],
                    'spu_id'        => $spu->id,
                    'item_no'       => $item_no,
                    'cover_image'   => $cover_image,
                    'category_id'   => $req['category_id_3'],
                    'category_id_1' => $req['category_id_1'],
                    'category_id_2' => $req['category_id_2'],
                    'category_id_3' => $req['category_id_3'],
                    'type_id'       => 1,
                    'brand_id'      => $brand->id,
                    'series_id'     => $series->id,
                    'sku_spec'      => json_encode($sku_spec, JSON_UNESCAPED_UNICODE),
                    'color_id'      => $color_id,
                    'goods_price'   => $diff_value['goods_price'],
                    'freight'       => $same_value['has_freight'] ? $same_value['freight'] : 0,
                    'stock'         => $diff_value['stock'],
                    'detail'        => $same_value['goods_detail'],
                    'is_sell'       => $same_value['is_sell'],
                    'sell_time'     => $same_value['is_sell'] ? null : $same_value['sell_time'],
                    'cloud_id'      => $cloud_sku_id,
                ]);
            }
        }

        return [
            'result'  => true,
            'message' => '发布成功',
        ];
    }

    public function edit($id)
    {
        $value = Models\SpecValue::where('spec_id', 2)->orderBy('name')->get()->pluck('name', 'id')->toArray();
        $result['data'] = $value;

        $goods_spu = Models\GoodsSpu::find($id);
        $cloud_spu = PModels\ItemSpu::findOrFail($goods_spu->cloud_id); // 如果云的被删了就没法玩了

        // 先取得云的sku和货号
        $cloud_sku_arr = $cloud_spu->sku->pluck('item_no', 'id')->all();
        $result['cloud_sku_arr'] = $cloud_sku_arr;

        // 再取商品的sku
        $goods_color_arr = $goods_spu->sku->each(function($item) {
            $item->color_name = $item->color->name;
        })->pluck('color_name', 'cloud_id')->all();
        $result['goods_color_arr'] = $goods_color_arr;

        // 整理商品版本信息
        $goods_data = [];
        $goods_size = [];
        foreach ($goods_spu->sku as $sku) {
            $sku->service_info = $sku->spu
                                     ->skuService($sku->color_id)
                                     ->get()
                                     ->pluck('name', 'id')
                                     ->all();
            $goods_data[$sku->cloud_id][] = $sku;

            $sku_spec = json_decode($sku->sku_spec, true);
            $goods_size[$sku->cloud_id][$sku_spec[1]['value']] = $sku;
            // [
            //     'goods_price'   => $sku->goods_price,
            //     'stock'         => $sku->stock,
            // ];
        }
        $result['goods_data'] = $goods_data;
        $result['goods_size'] = $goods_size;

        $category_info[0] = Models\Category::where('parent_id', 0)->get();
        $category_info[1] = Models\Category::where('parent_id', $goods_spu->category_id_1)->get();
        $category_info[2] = Models\Category::where('parent_id', $goods_spu->category_id_2)->get();
        $result['goods_spu']     = $goods_spu;
        $result['category_info'] = $category_info;

        return view('mall::store.goods.edit', $result);
    }

    public function update($id)
    {
        // 事务开始
        DB::beginTransaction();

        try {
            if (Request::input('goods_spu_id') != $id) {
                abort(403, '非法操作');
            }

            //验证条件
            $validator = Validator::make(Request::all(), [
                // 'cloud_id'                  => 'required|integer',
                'category_id_1'             => 'required|integer',
                'category_id_2'             => 'required|integer',
                'category_id_3'             => 'required|integer',
                'goods_info'                => 'required|array',
                'goods_info.*'              => 'required|array',
                'goods_info.*.color_name'   => 'required',
                'goods_info.*.goods_title'  => 'required',
                'goods_info.*.freight'      => 'nullable|numeric',
                'goods_info.*.has_freight'  => [
                    'required',
                    Rule::in([0, 1]),
                ],
                'goods_info.*.is_sell'      => [
                    'required',
                    Rule::in([0, 1]),
                ],
                'goods_info.*.sell_time'                => 'nullable|date',
                'goods_info.*.goods_detail'             => 'required',
                'goods_info.*.service'                  => 'array',
                'goods_info.*.service.*'                => 'integer',
                'goods_info.*.goods_size'               => 'required|array',
                'goods_info.*.goods_size.*'             => 'array',
                'goods_info.*.goods_size.*.stock'       => 'required|integer',
                'goods_info.*.goods_size.*.goods_price' => 'required|numeric',
                'goods_info.*.goods_image'              => 'required|array',
            ]);

            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $filesystem = new Filesystem();

            // 如果新增颜色，走创建，如果有少掉的颜色，走删除，如果还在的，走修改
            $goods_spu = Models\GoodsSpu::find($id);
            $cloud_spu = PModels\ItemSpu::findOrFail($goods_spu->cloud_id); // 如果云的被删了就没法玩了

            // 再取商品的sku
            $goods_color_arr = $goods_spu->sku->each(function($item) {
                $item->color_name = $item->color->name;
            })->pluck('color_name', 'cloud_id')->all();

            $all_color = array_keys(Request::input('goods_info'));
            $old_color = array_keys($goods_color_arr);

            $new_color = array_diff($all_color, $old_color);
            $del_color = array_diff($old_color, $all_color);
            $chg_color = array_intersect($all_color, $old_color);

            // 一、设置Brand、Series，不存在则新建（edit：更新云品牌和云系列的信息）
            $req = Request::all();
            // $cloud_spu = PModels\ItemSpu::findOrFail($req['cloud_id']); // 上面取过了
            $cloud_brand = $cloud_spu->brand;
            $cloud_series = $cloud_spu->series;
                //1、brand
            $brand = Models\Brand::updateOrCreate([
                'cloud_brand_id'    => $cloud_brand->id,
            ],[
                'name'              => $cloud_brand->name,
                'parent_id'         => 0,
                'type'              => 0,
                'initial'           => $cloud_brand->initial,
                'image'             => $cloud_brand->image,
            ]);
                //2、series
            $series = Models\Brand::updateOrCreate([
                'cloud_brand_id'    => $cloud_series->id,
            ],[
                'name'              => $cloud_series->name,
                'parent_id'         => $brand->id,
                'type'              => 1,
            ]);

            // 4、创建spu
            // $cloud_spu = PModels\ItemSpu::findOrFail($req['cloud_id']); // 上面取过了
            $goods_spu->brand_id      = $brand->id;
            $goods_spu->series_id     = $series->id;
            $goods_spu->category_id   = $req['category_id_3'];
            $goods_spu->category_id_1 = $req['category_id_1'];
            $goods_spu->category_id_2 = $req['category_id_2'];
            $goods_spu->category_id_3 = $req['category_id_3'];
            $goods_spu->service()->detach();

            if (!empty($del_color)) {
                $goods_spu->sku()->whereIn('cloud_id', $del_color)->forceDelete();
            }

            // 创建Service、Image、Sku
            // 二、创建spu（edit：更新spu，包括spu_spec和category_id）
            $color_ids = $size_ids = $sku_color_ids = [];

            foreach ($req['goods_info'] as $cloud_sku_id => $same_value) {
                if (!empty($new_color) && in_array($cloud_sku_id, $new_color)) {
                    // 先处理规格（颜色和鞋码）
                    $spec_value = Models\SpecValue::create(['name' => $same_value['color_name'], 'spec_id' => 1, 'store_id' => 1]);
                    $color_ids[] = $spec_value->id;
                    $sku_color_ids[$cloud_sku_id] = $spec_value->id;

                    foreach ($same_value['goods_size'] as $key => $value) {
                        if (!in_array($key, $size_ids)) {
                            $size_ids[] = $key;
                        }
                    }

                    $item_no = PModels\ItemSku::find($cloud_sku_id)->item_no;
                    $color_id = $sku_color_ids[$cloud_sku_id];

                    // 三、同一配色：创建Service
                        //5、mall_goods_service_mst
                    if (isset($same_value['service'])) {
                        $goods_spu->service()->attach($same_value['service'], ['color_id' => $color_id]);
                    }

                    // 四、同一配色：保存图片
                    $target_dir = public_path().sprintf('/uploads/mall/goods/%d/%d', $goods_spu->id, $color_id);

                    //存储图片&写数据库
                    foreach ($same_value['goods_image'] as $key => $path) {
                        //存储图片目标路径不存在则新建文件夹
                        if (!$filesystem->exists($target_dir)) {
                            $filesystem->makeDirectory($target_dir, 0755 ,true);
                        }

                        // 移动、复制图片
                        $target_filename = basename($path);
                        $target_path = sprintf('/uploads/mall/goods/%d/%d/%s', $goods_spu->id, $color_id, $target_filename);
                        if (starts_with($path, '/uploads/_tmp')) {
                            $filesystem->move(public_path().$path, public_path().$target_path);
                        } else {
                            $filesystem->copy(public_path().$path, public_path().$target_path);
                        }

                        // 6、保存到数据库 mall_goods_image
                        $create_images = Models\GoodsImage::create([
                            'spu_id'        => $goods_spu->id,
                            'color_id'      => $color_id,
                            'path'          => $target_path,
                            'sort_order'    => $key,
                        ]);

                        //如果是第一张图片，设该路径为$cover_image
                        if ($key == 0) {
                            $cover_image = $target_path;
                        }
                    }

                    // 五、不同配色：创建SKU
                        //7、SKU
                    foreach ($same_value['goods_size'] as $size_id => $diff_value) {
                        $sku_spec = $this->makeSpec($color_id, $size_id);
                        $sku = Models\Goods::create([
                            'name'          => $same_value['goods_title'],
                            'spu_id'        => $goods_spu->id,
                            'item_no'       => $item_no,
                            'cover_image'   => $cover_image,
                            'category_id'   => $req['category_id_3'],
                            'category_id_1' => $req['category_id_1'],
                            'category_id_2' => $req['category_id_2'],
                            'category_id_3' => $req['category_id_3'],
                            'type_id'       => 1,
                            'brand_id'      => $brand->id,
                            'series_id'     => $series->id,
                            'sku_spec'      => json_encode($sku_spec, JSON_UNESCAPED_UNICODE),
                            'color_id'      => $color_id,
                            'goods_price'   => $diff_value['goods_price'],
                            'freight'       => $same_value['has_freight'] ? $same_value['freight'] : 0,
                            'stock'         => $diff_value['stock'],
                            'detail'        => $same_value['goods_detail'],
                            'is_sell'       => $same_value['is_sell'],
                            'sell_time'     => $same_value['is_sell'] ? null : $same_value['sell_time'],
                            'cloud_id'      => $cloud_sku_id,
                        ]);
                    }
                } else {
                    $item_no = PModels\ItemSku::find($cloud_sku_id)->item_no;

                    // 1.通过 $cloud_sku_id 找到所有的sku
                    $old_sku = $goods_spu->sku()->where('cloud_id', $cloud_sku_id)->get();
                    $color_id = $old_sku->first()->color_id;

                    // 2.通过 sku 的 color_id 去更新一下颜色规格的信息
                    Models\SpecValue::where('id', $color_id)->update(['name' => $same_value['color_name']]);
                    $spec_value = Models\SpecValue::find($color_id);
                    $color_ids[] = $spec_value->id;
                    $sku_color_ids[$cloud_sku_id] = $spec_value->id;

                    // 3.处理鞋码规格，并计算出现有鞋码的规格值json
                    $sku_specs_json = []; // 现有鞋码的规格
                    foreach ($same_value['goods_size'] as $key => $value) {
                        if (!in_array($key, $size_ids)) {
                            $size_ids[] = $key;
                        }

                        $sku_spec = $this->makeSpec($color_id, $key);
                        $sku_specs_json[$key] = json_encode($sku_spec, JSON_UNESCAPED_UNICODE);
                    }

                    if (isset($same_value['service'])) {
                        $goods_spu->service()->attach($same_value['service'], ['color_id' => $color_id]);
                    }

                    // 4.更新同一配色的共同值，处理统一颜色的图片
                    $target_dir = public_path().sprintf('/uploads/mall/goods/%d/%d', $goods_spu->id, $color_id);

                    //存储图片&写数据库
                    $exist_old_images = $new_images = [];
                    foreach ($same_value['goods_image'] as $key => $path) {
                        //存储图片目标路径不存在则新建文件夹
                        if (!$filesystem->exists($target_dir)) {
                            $filesystem->makeDirectory($target_dir, 0755 ,true);
                        }

                        // 移动、复制图片
                        $target_filename = basename($path);
                        $target_path = sprintf('/uploads/mall/goods/%d/%d/%s', $goods_spu->id, $color_id, $target_filename);
                        if (starts_with($path, sprintf('/uploads/mall/goods/%d/%d/', $goods_spu->id, $color_id))) {
                            // 老图片
                            if ($key == 0) {
                                $cover_image = $path;
                            }

                            $exist_old_images[] = $path;

                            $old_sku->first()->images()->where('path', $path)->update(['sort_order' => $key]);
                            continue;
                        } elseif (starts_with($path, '/uploads/_tmp')) {
                            // 新上传
                            $filesystem->move(public_path().$path, public_path().$target_path);
                            $new_images[] = $target_path;
                        } else {
                            // 新选中的云图片
                            $filesystem->copy(public_path().$path, public_path().$target_path);
                            $new_images[] = $target_path;
                        }

                        // 6、保存到数据库 mall_goods_image
                        $create_images = Models\GoodsImage::create([
                            'spu_id'        => $goods_spu->id,
                            'color_id'      => $color_id,
                            'path'          => $target_path,
                            'sort_order'    => $key,
                        ]);

                        //如果是第一张图片，设该路径为$cover_image
                        if ($key == 0) {
                            $cover_image = $target_path;
                        }
                    }

                    // 取出所有的老图片
                    $old_sku->first()->images()
                                     ->whereNotIn('path', $new_images)
                                     ->whereNotIn('path', $exist_old_images)->forceDelete(); // TODO: 服务器上的图片删除

                    // 5.删除老鞋码，创建新鞋码
                    $goods_spu->sku()->where('cloud_id', $cloud_sku_id)->whereNotIn('sku_spec', $sku_specs_json)->forceDelete();

                    foreach ($same_value['goods_size'] as $size_id => $diff_value) {
                        $sku_spec = $sku_specs_json[$size_id];

                        $sku = Models\Goods::updateOrCreate([
                            'sku_spec'      => $sku_spec,
                        ], [
                            'name'          => $same_value['goods_title'],
                            'spu_id'        => $goods_spu->id,
                            'item_no'       => $item_no,
                            'cover_image'   => $cover_image,
                            'category_id'   => $req['category_id_3'],
                            'category_id_1' => $req['category_id_1'],
                            'category_id_2' => $req['category_id_2'],
                            'category_id_3' => $req['category_id_3'],
                            'type_id'       => 1,
                            'brand_id'      => $brand->id,
                            'series_id'     => $series->id,
                            'color_id'      => $color_id,
                            'goods_price'   => $diff_value['goods_price'],
                            'freight'       => $same_value['has_freight'] ? $same_value['freight'] : 0,
                            'stock'         => $diff_value['stock'],
                            'detail'        => $same_value['goods_detail'],
                            'is_sell'       => $same_value['is_sell'],
                            'sell_time'     => $same_value['is_sell'] ? null : $same_value['sell_time'],
                            'cloud_id'      => $cloud_sku_id,
                        ]);
                    }
                }
            }

            // 创建spu_spec
            $spu_spec            = $this->makeSpec($color_ids, $size_ids);
            $goods_spu->spu_spec = json_encode($spu_spec, JSON_UNESCAPED_UNICODE);
            $goods_spu->save();

            // 事务提交
            DB::commit();

            return [
                'result'  => true,
                'message' => '编辑成功',
            ];

        } catch(\Exception $e) {
            DB::rollBack();

            dd($e);

            return [
                'result'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function makeSpec($color_ids, $size_ids)
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

    public function recommend()
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $rec_ids = DB::table('product_recommend_item')->orderBy('sort_order')->get()
                         ->pluck('sku_id')->toArray();

            if (empty($rec_ids)) {
                $res['data'] = [];
            } else {
                $ids_ordered = implode(',', $rec_ids);
                $res['data'] = Models\ItemSku::select(['id', 'name', 'cover_image'])
                                             ->whereIn('id', $rec_ids)
                                             ->orderByRaw(DB::raw("FIELD(id, $ids_ordered)"))
                                             ->get();
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
