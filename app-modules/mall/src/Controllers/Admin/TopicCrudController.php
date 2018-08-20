<?php
namespace Modules\Mall\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Modules\Mall\Models;

class TopicCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Mall\Models\Topic");
        $this->crud->setEntityNameStrings('专题', '专题');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/mall/topic');

        $this->crud->setColumns([
            [
                'name'          => 'id',
                'label'         => 'ID',
            ],
            [
                'name'          => 'name',
                'label'         => '专题名称',
            ],
            [
                'name'          => 'image',
                'label'         => '图片',
                'type'          => "model_function",
                'function_name' => 'getImageHtml',
            ],
            [
                'name'          => 'goods_count',
                'label'         => '商品款数',
                'type'          => "model_function",
                'function_name' => 'getGoodsCountHtml',
            ],
            [
                'name'          => 'sort_order',
                'label'         => '排序',
            ],
        ]);

        $this->crud->addField([
            'name'              => 'name',
            'label'             => '专题名称',
            'type'              => 'text',
        ]);
        $this->crud->addField([
            'name'              => 'image',
            'label'             => '图片',
            'type'              => 'upload',
            'upload'            => true,
        ]);
        $this->crud->addField([
            'name'              => 'sort_order',
            'label'             => '排序',
            'type'              => 'text',
        ]);
    }

    public function index()
    {
        $this->crud->addClause('orderBy', 'sort_order', 'asc');

        return parent::index();
    }

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->getSaveAction();

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