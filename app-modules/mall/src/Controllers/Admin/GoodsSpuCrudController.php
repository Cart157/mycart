<?php

namespace Modules\Mall\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Modules\Mall\Models;
use Request;
use function GuzzleHttp\json_encode;

class GoodsSpuCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Mall\Models\GoodsSpu");
        $this->crud->setEntityNameStrings('商品', '商品');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/mall/goods-spu');
        //Column
        $this->crud->setColumns([
            [
                'name'          => 'id',
                'label'         => 'ID',
            ],
            [
                'name'          => 'name',
                'label'         => '商品全称',
            ],
            [
                'name'          => 'sku_count',
                'label'         => '产品款数',
                'type'          => "model_function",
                'function_name' => 'getSkuCountHtml',
            ],
            [
                'name'          => 'cover_image',
                'label'         => '商品封面',
                'type'          => 'model_function',
                'function_name' => 'getCoverImageHtml',
            ],
            [
                'name'          => 'category_id',
                'label'         => '商品类别',
                'type'          => 'select',
                'entity'        => 'category',
                'attribute'     => 'name',
            ],
            [
                'name'          => 'type_id',
                'label'         => '商品类型',
                'type'          => 'select',
                'entity'        => 'type',
                'attribute'     => 'name',
            ],
            [
                'name'          => 'brand_id',
                'label'         => '商品品牌',
                'type'          => 'select',
                'entity'        => 'brand',
                'attribute'     => 'name',
            ],
            [
                'name'          => 'series_id',
                'label'         => '商品系列',
                'type'          => 'select',
                'entity'        => 'series',
                'attribute'     => 'name',
            ],
            [
                'name'          => 'is_sell',
                'label'         => '上架状态',
                'type'          => 'boolean',
                'options'       => [0 => '未上架', 1 => '上架'],
            ],
            [
                'name'          => 'is_recommend',
                'label'         => '推荐状态',
                'type'          => 'boolean',
                'options'       => [0 => '不推荐', 1 => '推荐'],
            ],
            [
                'name'          => 'sort_order',
                'label'         => '排序',
            ],
        ]);
    }

    public function index()
    {
        return parent::index();
    }

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        //separator_basic
        $this->crud->addField([
            'name'              => 'separator_basic',
            'type'              => 'custom_html',
            'value'             => '++++++++++++++++++++++++++++++++++++++++ 基本信息 ++++++++++++++++++++++++++++++++++++++++',
        ]);
        //category_id
        $category_id = Request::input('cate_id') ?: 3;
        $category = Models\Category::find($category_id);
        $this->crud->addField([
            'name'              => 'category_id',
            'label'             => '商品类别',
            'type'              => 'select_spu_category',
            'model'             => 'Modules\Mall\Models\Category',
            'attribute'         => 'name',
            'value'             => $category->id,
        ]);
        //type_id
        $type_id = $category->type_id;
        $this->crud->addField([
            'name'              => 'type_id',
            'label'             => '商品类型',
            'type'              => 'hidden',
            'value'             => $type_id,
        ]);
        //name
        $this->crud->addField([
            'name'              => 'name',
            'label'             => '商品全称',
            'type'              => 'text',
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-6',
            ],
        ]);
        //sort_order
        $this->crud->addField([
            'name'              => 'sort_order',
            'label'             => '排序',
            'type'              => 'text',
            'default'           => 0,
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-6',
            ],
        ]);
        //goods_price
        $this->crud->addField([
            'name'              => 'goods_price',
            'label'             => '商品售价',
            'type'              => 'number',
            'prefix'            => '￥',
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-4',
            ],
        ]);
        //market_price
        $this->crud->addField([
            'name'              => 'market_price',
            'label'             => '市场售价',
            'type'              => 'number',
            'prefix'            => '￥',
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-4',
            ],
        ]);
        //official_price
        $this->crud->addField([
            'name'              => 'official_price',
            'label'             => '官方售价',
            'type'              => 'number',
            'prefix'            => '￥',
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-4',
            ],
        ]);
        //image
        $this->crud->addField([
            'name'              => 'cover_image',
            'label'             => '商品封面',
            'type'              => 'upload',
            'upload'            => true,
        ]);

        //separator_spec
        $this->crud->addField([
            'name'              => 'separator_spec',
            'type'              => 'custom_html',
            'value'             => '<br>++++++++++++++++++++++++++++++++++++++++ 规格管理 ++++++++++++++++++++++++++++++++++++++++<br>',
        ]);
        //spec
        $type = Models\Type::find($type_id);
        $this->type = $type;
        $this->addSpecValueField();

        //cloud_id
        $this->crud->addField([
            'label'                 => '绑定产品库云ID', // Table column heading
            'type'                  => 'select2_from_ajax',
            'name'                  => 'cloud_id', // the column that contains the ID of that connected entity
            'attribute'             => 'name', // foreign key attribute that is shown to user
            'model'                 => 'Modules\Product\Models\ItemSpu', // foreign key model
            'data_source'           => url("api/product/cloud-spu"), // url to controller search function (with /{id} should return model)
            'placeholder'           => "Select a Cloud Spu", // placeholder for the select
            'minimum_input_length'  => 0, // minimum characters to type before querying results
        ]);

        //separator_detail
        $this->crud->addField([
            'name'              => 'separator_detail',
            'type'              => 'custom_html',
            'value'             => '<br>++++++++++++++++++++++++++++++++++++++++ 详情描述 ++++++++++++++++++++++++++++++++++++++++<br>',
        ]);
        //brand_id
        $rs_brand = Models\Brand::where('type', 0)->get()->toArray();
        $brand = array_column($rs_brand, 'name', 'id');
        $this->crud->addField([
            'name'              => 'brand_id',
            'label'             => '商品品牌',
            'type'              => 'select2_from_array',
            'options'           => $brand,
            'allows_null'       => true,
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-6',
            ],
        ]);
        //series_id
        $rs_series = Models\Brand::where('type', 1)->get()->toArray();
        $series = array_column($rs_series, 'name', 'id');
        $this->crud->addField([
            'name'              => 'series_id',
            'label'             => '商品系列',
            'type'              => 'select2_from_array',
            'options'           => $series,
            'allows_null'       => true,
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-6',
            ],
        ]);
        //detail
        $this->crud->addField([
            'name'              => 'detail',
            'label'             => '商品描述',
            'type'              => 'wysiwyg',
        ]);

        //separator_logistics
        $this->crud->addField([
            'name'              => 'separator_logistics',
            'type'              => 'custom_html',
            'value'             => '<br>++++++++++++++++++++++++++++++++++++++++ 物流信息 ++++++++++++++++++++++++++++++++++++++++<br>',
        ]);
        //freight
        $this->crud->addField([
            'name'              => 'freight',
            'label'             => '运费',
            'type'              => 'number',
            'prefix'            => '￥',
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-4',
            ],
        ]);

        //separator_other
        $this->crud->addField([
            'name'              => 'separator_other',
            'type'              => 'custom_html',
            'value'             => '<br>++++++++++++++++++++++++++++++++++++++++ 其他信息 ++++++++++++++++++++++++++++++++++++++++<br>',
        ]);
        //is_sell
        $this->crud->addField([
            'name'              => 'is_sell',
            'label'             => '商品发布',
            'type'              => 'radio',
            'options'           => [0 => '放入仓库', 1 => '立即发布'],
            'inline'            => true,
        ]);
        //is_recommend
        $this->crud->addField([
            'name'              => 'is_recommend',
            'label'             => '商品推荐',
            'type'              => 'radio',
            'options'           => [0 => '否', 1 => '是'],
            'inline'            => true,
        ]);

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->getSaveAction();

        return view($this->crud->getCreateView(), $this->data);
    }

    public function store()
    {
        $crud_rs = parent::storeCrud();

        //获取req_specs
        $req_specs = Request::input('specs');
        if (!isset($req_specs[1])) {
            $req_specs[1] = [];
            ksort($req_specs);
        }

        //获取req_customs
        $req_customs = Request::input('customs');

        //1、保存自定义规格值到mall_spec_value
        foreach ($req_customs as $req_custom) {
            if ($req_custom == null) {
                continue;
            }
            $spec_value = Models\SpecValue::create([
                'name'      => $req_custom,
                'spec_id'   => 1,
                'store_id'  => 1,
            ]);
            //并存进req_specs
            array_push($req_specs[1], (string)$spec_value->id);
        }

        //生成spu_spec
        foreach ($req_specs as $spec_id => $value) {
            $spec = Models\Spec::find($spec_id);

            $spu_spec[] = [
                'spec_id'	=> $spec_id,
                'spec_name'	=> $spec->name,
                'value'		=> $value,
            ];
        }

        //2、保存spu_spec到SPU
        $this->data['entry']->spu_spec = json_encode($spu_spec, JSON_UNESCAPED_UNICODE);
        $this->data['entry']->save();

        //规格笛卡尔积得names
        $names = $this->dikaer($req_specs);
        $count = count($names);

        //规格笛卡尔积得sku_spec_tmps
        $sku_spec_tmps = $this->dikaer2($req_specs);

        //生成sku_specs
        foreach ($sku_spec_tmps as $i => $sku_spec_tmp) {
            foreach ($sku_spec_tmp as $spec_id => $value) {
                $spec = Models\Spec::find($spec_id);
                // XXX:sku_specs格式有待商榷
                $sku_specs[$i][] = [
                    'spec_id'	=> $spec_id,
                    'spec_name'	=> $spec->name,
                    'value'		=> $value,
                ];
            }
        }

        //3、规格循环写入SKU
        for ($i = 0; $i < $count; $i++) {
            $goods = Models\Goods::create([
                'spu_id'            => $this->data['entry']->id,
                'name'              => $this->data['entry']->name.' '.$names[$i],
                'category_id'       => $this->data['entry']->category_id,
                'type_id'           => $this->data['entry']->type_id,
                'brand_id'          => $this->data['entry']->brand_id,
                'series_id'         => $this->data['entry']->series_id,
                'sku_spec'          => json_encode($sku_specs[$i], JSON_UNESCAPED_UNICODE),
                'color_id'          => $sku_spec_tmps[$i][1],
                'goods_price'       => $this->data['entry']->goods_price,
                'market_price'      => $this->data['entry']->market_price,
                'official_price'    => $this->data['entry']->official_price,
                'detail'            => $this->data['entry']->detail,
                'is_sell'           => $this->data['entry']->is_sell,
                'is_recommend'      => $this->data['entry']->is_recommend,
            ]);
            $goods->save();
        }

        return $crud_rs;
    }

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $this->data['entry'] = $this->crud->getEntry($id);

        //separator_basic
        $this->crud->addField([
            'name'              => 'separator_basic',
            'type'              => 'custom_html',
            'value'             => '++++++++++++++++++++++++++++++++++++++++ 基本信息 ++++++++++++++++++++++++++++++++++++++++',
        ]);
        //category_id
        $this->crud->addField([
            'name'              => 'category_id',
            'label'             => '商品类别',
            'type'              => 'select2',
            'model'             => 'Modules\Mall\Models\Category',
            'attribute'         => 'name',
            'attributes'        => [
                'disabled'      => 'disabled',
            ],
        ]);
        //name
        $this->crud->addField([
            'name'              => 'name',
            'label'             => '商品全称',
            'type'              => 'text',
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-6',
            ],
        ]);
        //sort_order
        $this->crud->addField([
            'name'              => 'sort_order',
            'label'             => '排序',
            'type'              => 'text',
            'default'           => 0,
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-6',
            ],
        ]);
        //goods_price
        $this->crud->addField([
            'name'              => 'goods_price',
            'label'             => '商品售价',
            'type'              => 'number',
            'prefix'            => '￥',
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-4',
            ],
        ]);
        //market_price
        $this->crud->addField([
            'name'              => 'market_price',
            'label'             => '市场售价',
            'type'              => 'number',
            'prefix'            => '￥',
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-4',
            ],
        ]);
        //official_price
        $this->crud->addField([
            'name'              => 'official_price',
            'label'             => '官方售价',
            'type'              => 'number',
            'prefix'            => '￥',
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-4',
            ],
        ]);
        //image
        $this->crud->addField([
            'name'              => 'cover_image',
            'label'             => '商品封面',
            'type'              => 'upload',
            'upload'            => true,
        ]);

        //separator_spec
        $this->crud->addField([
            'name'              => 'separator_spec',
            'type'              => 'custom_html',
            'value'             => '<br>++++++++++++++++++++++++++++++++++++++++ 规格管理 ++++++++++++++++++++++++++++++++++++++++<br>',
        ]);
        //spec
            //type
        $type = Models\Type::find($this->data['entry']->type_id);
        $this->type = $type;
            //spu_spec
        $spu_spec_tmp = json_decode($this->data['entry']->spu_spec, true);
        $spu_spec_value = array_column($spu_spec_tmp, 'value', 'spec_id');
        $this->addSpecValueField($spu_spec_value);

        //cloud_id
        $this->crud->addField([
            'label'                 => '绑定产品库云ID', // Table column heading
            'type'                  => 'select2_from_ajax',
            'name'                  => 'cloud_id', // the column that contains the ID of that connected entity
            'attribute'             => 'name', // foreign key attribute that is shown to user
            'model'                 => 'Modules\Product\Models\ItemSpu', // foreign key model
            'data_source'           => url("api/product/cloud-spu"), // url to controller search function (with /{id} should return model)
            'placeholder'           => "Select a Cloud Spu", // placeholder for the select
            'minimum_input_length'  => 0, // minimum characters to type before querying results
        ]);

        //separator_detail
        $this->crud->addField([
            'name'              => 'separator_detail',
            'type'              => 'custom_html',
            'value'             => '<br>++++++++++++++++++++++++++++++++++++++++ 详情描述 ++++++++++++++++++++++++++++++++++++++++<br>',
        ]);
        //brand_id
        $rs_brand = Models\Brand::where('type', 0)->get()->toArray();
        $brand = array_column($rs_brand, 'name', 'id');
        $this->crud->addField([
            'name'              => 'brand_id',
            'label'             => '商品品牌',
            'type'              => 'select2_from_array',
            'options'           => $brand,
            'allows_null'       => true,
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-6',
            ],
        ]);
        //series_id
        $rs_series = Models\Brand::where('type', 1)->get()->toArray();
        $series = array_column($rs_series, 'name', 'id');
        $this->crud->addField([
            'name'              => 'series_id',
            'label'             => '商品系列',
            'type'              => 'select2_from_array',
            'options'           => $series,
            'allows_null'       => true,
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-6',
            ],
        ]);
        //detail
        $this->crud->addField([
            'name'              => 'detail',
            'label'             => '商品描述',
            'type'              => 'wysiwyg',
        ]);

        //separator_logistics
        $this->crud->addField([
            'name'              => 'separator_logistics',
            'type'              => 'custom_html',
            'value'             => '<br>++++++++++++++++++++++++++++++++++++++++ 物流信息 ++++++++++++++++++++++++++++++++++++++++<br>',
        ]);
        //freight
        $this->crud->addField([
            'name'              => 'freight',
            'label'             => '运费',
            'type'              => 'number',
            'prefix'            => '￥',
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-4',
            ],
        ]);

        //separator_other
        $this->crud->addField([
            'name'              => 'separator_other',
            'type'              => 'custom_html',
            'value'             => '<br>++++++++++++++++++++++++++++++++++++++++ 其他信息 ++++++++++++++++++++++++++++++++++++++++<br>',
        ]);
        //is_sell
        $this->crud->addField([
            'name'              => 'is_sell',
            'label'             => '商品发布',
            'type'              => 'radio',
            'options'           => [0 => '放入仓库', 1 => '立即发布'],
            'inline'            => true,
        ]);
        //is_recommend
        $this->crud->addField([
            'name'              => 'is_recommend',
            'label'             => '商品推荐',
            'type'              => 'radio',
            'options'           => [0 => '否', 1 => '是'],
            'inline'            => true,
        ]);

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->getSaveAction();
        $this->data['fields'] = $this->crud->getUpdateFields($id);
        $this->data['title'] = trans('base::crud.edit').' '.$this->crud->entity_name;
        $this->data['id'] = $id;

        return view($this->crud->getEditView(), $this->data);
    }

    public function update()
    {
        $crud_rs = parent::updateCrud();

        //获取req_specs
        $req_specs = Request::input('specs');
        if (!isset($req_specs[1])) {
            $req_specs[1] = [];
            ksort($req_specs);
        }

        //获取req_customs
        $req_customs = Request::input('customs');

        //1、保存自定义规格值到mall_spec_value
        foreach ($req_customs as $req_custom) {
            if ($req_custom == null) {
                continue;
            }

            if (Models\SpecValue::find($req_custom)) {
                //存进req_specs
                array_push($req_specs[1], $req_custom);
                continue;
            }

            $spec_value = Models\SpecValue::create([
                'name'      => $req_custom,
                'spec_id'   => 1,
                'store_id'  => 1,
            ]);
            //存进req_specs
            array_push($req_specs[1], (string)$spec_value->id);
        }

        //生成spu_spec
        foreach ($req_specs as $spec_id => $value) {
            $spec = Models\Spec::find($spec_id);

            $spu_spec[] = [
                'spec_id'	=> $spec_id,
                'spec_name'	=> $spec->name,
                'value'		=> $value,
            ];
        }

        //2、保存spu_spec到SPU
        $this->data['entry']->spu_spec = json_encode($spu_spec, JSON_UNESCAPED_UNICODE);
        $this->data['entry']->save();

        //规格笛卡尔积得names
        $names = $this->dikaer($req_specs);
        $count = count($names);

        //规格笛卡尔积得sku_spec_tmps
        $sku_spec_tmps = $this->dikaer2($req_specs);

        //生成sku_specs、sku_specs_json
        foreach ($sku_spec_tmps as $i => $sku_spec_tmp) {
            foreach ($sku_spec_tmp as $spec_id => $value) {
                $spec = Models\Spec::find($spec_id);

                $sku_specs[$i][] = [
                    'spec_id'	=> $spec_id,
                    'spec_name'	=> $spec->name,
                    'value'		=> $value,
                ];
            }
            $sku_specs_json[$i] = json_encode($sku_specs[$i], JSON_UNESCAPED_UNICODE);
        }

        //删除取消的规格的模型
        $cancel = Models\Goods::where('spu_id', $this->data['entry']->id)->whereNotIn('sku_spec', $sku_specs_json)->forceDelete();

        //更新已存在的brand_id和series_id
        $upd = Models\Goods::where('spu_id', $this->data['entry']->id)->update([
            'brand_id'  => $this->data['entry']->brand_id,
            'series_id' => $this->data['entry']->series_id,
        ]);

        //取已存在的规格数组
        $in = Models\Goods::select('sku_spec')
                            ->where('spu_id', $this->data['entry']->id)
                            ->get()
                            ->toArray();
        $in = array_column($in, 'sku_spec');

        //规格循环写入SKU,存在则跳过
        for ($i = 0; $i < $count; $i++) {
            if (in_array($sku_specs_json[$i], $in)) {
                $goods = Models\Goods::where('sku_spec', $sku_specs_json[$i])->first();
                $goods->name = $this->data['entry']->name.' '.$names[$i];
                $goods->save();
            } else {
                $goods = Models\Goods::create([
                    'spu_id'            => $this->data['entry']->id,
                    'name'              => $this->data['entry']->name.' '.$names[$i],
                    'category_id'       => $this->data['entry']->category_id,
                    'type_id'           => $this->data['entry']->type_id,
                    'brand_id'          => $this->data['entry']->brand_id,
                    'series_id'         => $this->data['entry']->series_id,
                    'sku_spec'          => $sku_specs_json[$i],
                    'color_id'          => $sku_spec_tmps[$i][1],
                    'goods_price'       => $this->data['entry']->goods_price,
                    'market_price'      => $this->data['entry']->market_price,
                    'official_price'    => $this->data['entry']->official_price,
                    'detail'            => $this->data['entry']->detail,
                ]);
            }
        }

        return $crud_rs;
    }

    protected function addSpecValueField($spu_spec_value = [])
    {
        foreach ($this->type->specs as $spec) {
            $this->crud->addField([
                'name'              => 'specs['.$spec->id.']',
                'label'             => $spec->name,
                'type'              => 'checklist2',
                'attribute'         => 'name',
                'model'             => 'Modules\Mall\Models\SpecValue',
                'spec_id'           => $spec->id,
                'store_id'          => 0,
                'value'             => isset($spu_spec_value[$spec->id]) ? $spu_spec_value[$spec->id] : [],
            ]);
            if ($spec->id == 1) {
                $this->crud->addField([
                    'name'          => 'customs[]',
                    'label'         => '',
                    'type'          => 'textplus2',
                    'model'         => 'Modules\Mall\Models\SpecValue',
                    'attribute'     => 'name',
                    'store_id'      => 1,
                    'value'         => isset($spu_spec_value[1]) ? $spu_spec_value[1] : null,
                ]);
            }
        }
    }

    //计算alias_name方法
    protected function dikaer($req_specs)
    {
        $result = array_shift($req_specs);

        if (empty($req_specs)) {
            $arr0 = $result;
            $result = [];
            foreach ($arr0 as $val0) {
                $val0 = Models\SpecValue::find($val0)->name;
                $result[] = $val0;
            }
            return $result;
        }

        while ($arr2 = array_shift($req_specs)) {
            $arr1 = $result;
            $result = [];
            foreach ($arr1 as $val1) {
                foreach($arr2 as $val2) {
                    if (Models\SpecValue::find($val1)) {
                        $val1 = Models\SpecValue::find($val1)->name;
                    }
                    $val2 = Models\SpecValue::find($val2)->name;
                    $result[] = $val1.' '.$val2;
                }
            }
        }
        return $result;
    }

    //计算sku_spec方法
    protected function dikaer2($req_specs)
    {
        //取所有键
        $res_key_all = array_keys($req_specs);
        //用于array_combine的key个数，第一次取2个
        $i = 2;
        //取第一个数组
        $result = array_shift($req_specs);

        //当只选一个规格
        if (empty($req_specs)) {
            $arr0 = $result;
            $result = [];
            foreach ($arr0 as $val0) {
                if (!is_array($val0)) {
                    $val0 = [$val0];
                }
                $result[] = array_combine($res_key_all, $val0);
            }
            return $result;
        }

        //当存在多个规格
        while ($arr2 = array_shift($req_specs)) {//$arr2作为array_merge的第2个参数
            $res_key = array_slice($res_key_all, 0, $i);//取所有键的前$i个做为array_combine的key
            $arr1 = $result;//$arr1接收上一次结果，作为array_merge的第1个参数
            $result = [];//$result用于接收结果，先清空之前结果
            foreach ($arr1 as $val1) {
                foreach($arr2 as $val2) {
                    if (!is_array($val1)) {
                        $val1 = [$val1];
                    }
                    if (!is_array($val2)) {
                        $val2 = [$val2];
                    }
                    $result[] = array_combine($res_key, array_merge($val1,$val2));
                }
            }
            //循环每结束一次，下次从所有键array_slice多取一个
            $i++;
        }
        return $result;
    }
}