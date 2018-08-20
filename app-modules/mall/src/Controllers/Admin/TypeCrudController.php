<?php

namespace Modules\Mall\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Database\Eloquent\Model;
use Modules\Mall\Models;
use Request;

class TypeCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Mall\Models\Type");
        $this->crud->setEntityNameStrings('类型', '类型');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/mall/type');
        //Column
        $this->crud->setColumns([
            [
                'name'          => 'id',
                'label'         => 'ID',
            ],
            [
                'name'          => 'name',
                'label'         => '名称',
            ],
            [
                'name'          => 'specs',
                'label'         => '关联的规格',
                'type'          => 'select_multiple',
                'entity'        => 'specs',
                'attribute'     => 'name',
                'model'         => 'Modules\Mall\Models\Spec',
            ],
            [
                'name'          => 'sort_order',
                'label'         => '排序',
            ],
        ]);
        //Field
        $this->crud->addField([
            'name'       => 'name',
            'label'      => '名称',
            'type'       => 'text',
        ]);
        $this->crud->addField([
            'name'       => 'sort_order',
            'label'      => '排序',
            'type'       => 'text',
            'default'    => 0,
        ]);
        $this->crud->addField([
            'name'      => 'specs',
            'label'     => '选择关联规格',
            'type'      => 'checklist',
            'entity'    => 'specs',
            'attribute' => 'name',
            'model'     => 'Modules\Mall\Models\Spec',
            'pivot'     => true,
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
        $crud_rs = parent::storeCrud();
/*
        $id = $this->data['entry']->id;
        $specs = Request::instance()->specs;

        $type = Models\Type::find($id);
        $type->specs()->attach($specs);
*/
        return $crud_rs;
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
        $crud_rs = parent::updateCrud();
/*
        $id = $this->data['entry']->id;
        $specs = Request::instance()->specs;

        $type = Models\Type::find($id);
        $type->specs()->sync($specs);
*/
        return $crud_rs;
    }
}
