<?php

namespace Modules\Mall\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Modules\Mall\Models;
use DB;

class BrandCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Mall\Models\Brand");
        $this->crud->setEntityNameStrings('品牌', '品牌');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/mall/brand');
//        $this->crud->denyAccess(['create', 'delete']);
        $this->crud->setColumns([
            [
                'name'          => 'id',
                'label'         => 'ID',
            ],
            [
                'name'          => 'name',
                'label'         => '品牌名称',
                'type'          => "model_function",
                'function_name' => 'getNameTreeHtml',
            ],
            [
                'name'          => 'parent_id',
                'label'         => '父级品牌',
                'type'          => 'select',
                'entity'        => 'parent',
                'attribute'     => 'name',
                'model'         => 'Modules\Mall\Models\Brand',
            ],
            [
                'name'          => 'type',
                'label'         => '类型',
                'type'          => 'boolean',
                'options'       => [0 => '品牌', 1 => '系列'],
            ],
            [
                'name'          => 'initial',
                'label'         => '首字母',
            ],
            [
                'name'          => 'image',
                'label'         => '图片',
                'type'          => 'model_function',
                'function_name' => 'getImageHtml',
            ],
            [
                'label'         => '排序',
                'name'          => 'sort_order',
            ],
        ]);
    }

    /**
     * Display all rows in the database for this entity.
     * This overwrites the default CrudController behaviour:
     * - instead of showing all entries, only show the "active" ones.
     *
     * @return Response
     */
    public function index()
    {
        $tree_ids = Models\Brand::treeIds();

        if (!empty($tree_ids)) {
            $ids_ordered = implode(',', $tree_ids);
            $this->crud->addClause('orderByRaw', DB::raw("FIELD(id, $ids_ordered)"));
        }

        return parent::index();
    }

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->getSaveAction();

        $this->crud->addField([
            'name'       => 'name',
            'label'      => '品牌名称',
            'type'       => 'text',
        ]);
        $this->crud->addField([
            'name'      => 'sort_order',
            'label'     => '排序',
            'type'      => 'text',
        ]);
        $this->crud->addField([
            'name'      => 'parent_id',
            'label'     => '父级品牌',
            'type'      => 'select2',
            'attribute' => 'name',
            'model'     => 'Modules\Mall\Models\Brand',
        ]);
        $this->crud->addField([
            'name'       => 'type',
            'label'      => '类型',
            'type'       => 'select2_from_array',
            'options'    => [null => '请选择', 0 => '品牌', 1 => '系列'],
        ]);
        $this->crud->addField([
            'name'       => 'initial',
            'label'      => '首字母',
            'type'       => 'text',
        ]);
        $this->crud->addField([
            'name'       => 'image',
            'label'      => '图片',
            'type'       => 'upload',
            'upload'     => true,
        ]);

        return view($this->crud->getCreateView(), $this->data);
    }

    public function store()
    {
        return parent::storeCrud();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $this->data['entry'] = $this->crud->getEntry($id);
        //$this->crud->addField((array) json_decode($this->data['entry']->field)); // <---- this is where it's different
        $this->crud->addField([
            'name'       => 'name',
            'label'      => '品牌名称',
            'type'       => 'text',
        ]);
        $this->crud->addField([
            'name'      => 'sort_order',
            'label'     => '排序',
            'type'      => 'text',
        ]);
        $this->crud->addField([
            'name'      => 'parent_id',
            'label'     => '父级品牌',
            'type'      => 'select2',
            'entity'    => 'parent',
            'attribute' => 'name',
            'model'     => 'Modules\Mall\Models\Brand',
        ]);
        $this->crud->addField([
            'name'       => 'type',
            'label'      => '类型',
            'type'       => 'select2_from_array',
            'options'    => [null => '请选择', 0 => '品牌', 1 => '系列'],
        ]);
        $this->crud->addField([
            'name'       => 'initial',
            'label'      => '首字母',
            'type'       => 'text',
        ]);
        $this->crud->addField([
            'name'       => 'image',
            'label'      => '图片',
            'type'       => 'upload',
            'upload'     => true,
        ]);

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->getSaveAction();
        $this->data['fields'] = $this->crud->getUpdateFields($id);
        $this->data['title'] = trans('base::crud.edit').' '.$this->crud->entity_name;

        $this->data['id'] = $id;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }

    public function update()
    {
        return parent::updateCrud();
    }
}
