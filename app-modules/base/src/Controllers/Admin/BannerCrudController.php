<?php

namespace Modules\Base\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Request;

class BannerCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Base\Models\Banner");
        $this->crud->setEntityNameStrings('Banner', 'Banner');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/banner');

        $this->crud->setColumns([
            [
                'name'          => 'id',
                'label'         => 'ID',
            ],
            [
                'name'          => 'type_id',
                'label'         => '类型',
                'type'          => 'select',
                'entity'        => 'type',
                'attribute'     => 'name',
            ],
            [
                'name'          => 'item_id',
                'label'         => '功能ID',
            ],
            [
                'name'          => 'image',
                'label'         => '图片',
                'type'          => 'model_function',
                'function_name' => 'getImageHtml',
            ],
            [
                'name'          => 'sort_order',
                'label'         => '排序',
            ],
        ]);

        $this->crud->addField([
            'name'      => 'type_id',
            'label'     => '类型',
            'type'      => 'select',
            'attribute' => 'name',
            'model'     => 'Modules\Base\Models\FunType',
        ]);
        $this->crud->addField([
            'name'      => 'item_id',
            'label'     => '功能ID',
            'type'      => 'number',
        ]);
        $this->crud->addField([
            'name'      => 'image',
            'label'     => '图片',
            'type'      => 'upload',
            'upload'    => true,
        ]);
        $this->crud->addField([
            'name'      => 'sort_order',
            'label'     => '排序',
            'type'      => 'number',
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
