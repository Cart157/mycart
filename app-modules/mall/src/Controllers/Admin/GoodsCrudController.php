<?php

namespace Modules\Mall\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Filesystem\Filesystem;
use Modules\Mall\Models;
use Request;
use Illuminate\Support\Facades\Config;
use GuzzleHttp;

class GoodsCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Mall\Models\Goods");
        $this->crud->setEntityNameStrings('商品款式', '商品款式');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/mall/goods');

        $this->crud->setColumns([
            [
                'name'          => 'id',
                'label'         => 'ID',
            ],
            [
                'name'          => 'name',
                'label'         => '商品名称（sku）',
            ],
            [
                'name'          => 'item_no',
                'label'         => '货号',
            ],
            [
                'name'          => 'cover_image',
                'label'         => '商品封面',
                'type'          => 'model_function',
                'function_name' => 'getCoverImageHtml',
            ],
            [
                'name'          => 'spu_id',
                'label'         => '所属SPU',
                'type'          => 'select',
                'entity'        => 'spu',
                'attribute'     => 'name',
            ],
            [
                'name'          => 'stock',
                'label'         => '库存',
            ],
            [
                'name'          => 'sort_order',
                'label'         => '排序',
            ],
        ]);
    }

    public function index()
    {
        $this->crud->addButtonFromModelFunction('top', 'goodsspu_list', 'btnReturnGoodsSpu');

        $this->crud->denyAccess(['create']);

        if (Request::has('spu_id')) {
            $this->crud->addClause('where', 'spu_id', Request::input('spu_id'));
        }

        if (Request::has('topic_id')) {
            $this->crud->addClause('whereHas', 'topic', function($query) {
                $query->where('topic_id', Request::input('topic_id'));
            });
        }

        return parent::index();
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
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-6',
            ],
        ]);
        //spu_name
        $this->crud->addField([
            'name'              => 'spu_name',
            'label'             => '所属商品',
            'type'              => 'text',
            'value'             => $this->data['entry']->spu->name,
            'attributes'        => [
                'disabled'      => 'disabled',
            ],
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-6',
            ],
        ]);
        //spu_id
        $this->crud->addField([
            'name'              => 'spu_id',
            'type'              => 'hidden',
        ]);
        //name
        $this->crud->addField([
            'name'              => 'name',
            'label'             => '商品名称（sku）',
            'type'              => 'text',
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-6',
            ],
        ]);
        //item_no
        $this->crud->addField([
            'name'              => 'item_no',
            'label'             => '货号',
            'type'              => 'text',
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-3',
            ],
        ]);
        //sort_order
        $this->crud->addField([
            'name'              => 'sort_order',
            'label'             => '排序',
            'type'              => 'text',
            'default'           => 0,
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-3',
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
        //stock
        $this->crud->addField([
            'name'              => 'stock',
            'label'             => '商品库存',
            'type'              => 'number',
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-4',
            ],
        ]);
        //images
        $images = Models\GoodsImage::where('spu_id', $this->data['entry']->spu_id)->where('color_id', $this->data['entry']->color_id)->orderBy('sort_order', 'asc')->get();
        $this->crud->addField([
            'label'             => '商品图片',
            'type'              => 'imgupload',
            'name'              => 'goods_images[]',
            'value'             => $images,
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
            //sku_spec
        $sku_spec_tmp = json_decode($this->data['entry']->sku_spec, true);
        $sku_spec_tmp = array_column($sku_spec_tmp, 'value', 'spec_id');
        if (!is_null($sku_spec_tmp)) {
            foreach ($sku_spec_tmp as $key => $value) {
                $sku_spec_value[$key] = [$value];
            }
        }
        $this->addSpecValueField($sku_spec_value);

        //cloud_id
        $spu_cloud_id = $this->data['entry']->spu->cloud_id;
        $this->crud->addField([
            'label'                 => '绑定产品库云ID', // Table column heading
            'type'                  => 'select2_from_ajax',
            'name'                  => 'cloud_id', // the column that contains the ID of that connected entity
            'attribute'             => 'item_no', // foreign key attribute that is shown to user
            'model'                 => 'Modules\Product\Models\ItemSku', // foreign key model
            'data_source'           => url("api/product/item-spu/{$spu_cloud_id}/cloud-sku"), // url to controller search function (with /{id} should return model)
            'placeholder'           => "Select a Cloud Sku", // placeholder for the select
            'minimum_input_length'  => 0, // minimum characters to type before querying results
            'allows_null'           => true,
        ]);

        //separator_detail
        $this->crud->addField([
            'name'              => 'separator_detail',
            'type'              => 'custom_html',
            'value'             => '<br>++++++++++++++++++++++++++++++++++++++++ 详情描述 ++++++++++++++++++++++++++++++++++++++++<br>',
        ]);
        //brand_id
        $this->crud->addField([
            'name'              => 'brand_id',
            'label'             => '商品品牌',
            'type'              => 'select2',
            'model'             => 'Modules\Mall\Models\Brand',
            'attribute'         => 'name',
            'attributes'        => [
                'disabled'      => 'disabled',
            ],
            'wrapperAttributes' => [
                'class'         => 'form-group col-md-6',
            ],
        ]);
        //series_id
        $this->crud->addField([
            'name'              => 'series_id',
            'label'             => '商品系列',
            'type'              => 'select2',
            'model'             => 'Modules\Mall\Models\Brand',
            'attribute'         => 'name',
            'attributes'        => [
                'disabled'      => 'disabled',
            ],
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

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->getSaveAction();
        $this->data['fields'] = $this->crud->getUpdateFields($id);
        $this->data['title'] = trans('base::crud.edit').' '.$this->crud->entity_name;
        $this->data['id'] = $id;

        return view($this->crud->getEditView(), $this->data);
    }

    public function update()
    {
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/mall/goods?spu_id='.Request::input('spu_id'));

        $rs = parent::updateCrud();

        //取模型
        $spu = $this->data['entry']->spu;
        $sku = $this->data['entry'];

        if (Request::has('cloud_id')) {
            // 取得云端产品库的信息
            $goods_obj = Models\Goods::find($sku->id);
            $cloud_id = $goods_obj->cloud_id;

            $http = new GuzzleHttp\Client;
            $response = $http->get("http://www.la-dev.com/api/product/item-sku/{$cloud_id}");
            $json_arr =  json_decode((string) $response->getBody(), true);

            $goods_obj->sku_attr = json_encode($json_arr['data']['attr_info'], JSON_UNESCAPED_UNICODE);
            $goods_obj->save();
        }

        if (Request::has('goods_images')) {
            //取旧图片信息
            $old_images = Models\GoodsImage::where('spu_id', $spu->id)->where('color_id', $sku->color_id)->get();

            //取新图片信息
            $new_sort = [];
            $new_images = Request::input('goods_images');

            //设置存储图片目标路径
            $filesystem = new Filesystem();
            $target_dir = public_path().sprintf('/uploads/mall/goods/%d/%d', $spu->id, $sku->color_id);

            //存储图片&写数据库
            foreach ($new_images as $key => $path) {
                //存储图片目标路径不存在则新建文件夹
                if (!$filesystem->exists($target_dir)) {
                    $filesystem->makeDirectory($target_dir, 0755 ,true);
                }

                //如果是临时文件
                if (starts_with($path, '/uploads/_tmp')) {
                    //移动图片
                    $target_filename = substr(strchr(basename($path), '_'), 1);
                    $target_path = sprintf('/uploads/mall/goods/%d/%d/%s', $spu->id, $sku->color_id, $target_filename);
                    $filesystem->move(public_path().$path, public_path().$target_path);

                    //保存地址
                    $create_images = Models\GoodsImage::create([
                        'spu_id'        => $spu->id,
                        'color_id'      => $sku->color_id,
                        'path'          => $target_path,
                        'sort_order'    => $key,
                    ]);
                } else {
                    //记录更改的sort_order
                    $new_sort[$path] = $key;
                }

                //如果是第一张图片，更新相同spu_id和color_id的cover_image
                if ($key == 0) {
                    $cover_image = isset($target_path) ? $target_path : $path;
                    $upd = Models\Goods::where('spu_id', $spu->id)->where('color_id', $sku->color_id)->update([
                        'cover_image'   => $cover_image,
                    ]);
                }
            }

            //更新已存在图片的sort_order
            foreach ($old_images as $old_image) {
                if (isset($new_sort[$old_image->path])) {
                    $old_image->sort_order = $new_sort[$old_image->path];
                    $old_image->save();
                } else {
                    // 删服务器上的图片文件
                    $filesystem = new Filesystem();
                    @$filesystem->delete(public_path().$old_image->path);

                    $old_image->forceDelete();
                }
            }
        }
        return $rs;
    }

    protected function addSpecValueField($sku_spec_value = [])
    {
        foreach ($this->type->specs as $spec) {
            $this->crud->addField([
                'name'              => 'spec['.$spec->id.']',
                'label'             => $spec->name,
                'type'              => 'checklist2',
                'attribute'         => 'name',
                'model'             => 'Modules\Mall\Models\SpecValue',
                'spec_id'           => $spec->id,
                'value'             => isset($sku_spec_value[$spec->id]) ? $sku_spec_value[$spec->id] : [],
            ]);
        }
    }
}