<?php

namespace Modules\Mall\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Modules\Mall\Models;
use Request;

class SpecValueCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Mall\Models\SpecValue");
        $this->crud->setEntityNameStrings('规格值', '规格值');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/mall/spec-value');

        $this->crud->setColumns([
            [
                'name'  => 'id',
                'label' => 'ID',
            ],
            [
                'name'  => 'name',
                'label' => '名称',
            ],
            [
                'name'  => 'specification',
                'label' => '所属规格',
                'type'  => 'select',
                'entity'    => 'spec',
                'attribute' => 'name',
            ],
            [
                'name'  => 'sort_order',
                'label' => '排序',
            ],
        ]);
        
        $rs_spec = Models\Spec::all()->toArray();
        $spec = array_column($rs_spec, 'name', 'id');
        $this->crud->addFilter([ // add a "simple" filter called Draft
            'type' => 'dropdown',
            'name' => 'spec_id',
            'label'=> '规格'
        ],
        $spec,
        function($value) {
            $this->crud->addClause('where', 'spec_id', $value);
        });
    }

    public function index()
    {
        $this->crud->denyAccess(['create']);

        $this->crud->addButtonFromModelFunction('top', 'spec', 'btnReturnSpec');

        $this->crud->addClause('orderBy', 'spec_id', 'asc');
        $this->crud->addClause('orderBy', 'sort_order', 'asc');
//         if (Request::has('spec_id')) {
//             $this->crud->addClause('where', 'spec_id', Request::input('spec_id'));
//         }

        return parent::index();
    }

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        // 不传spec_id，就直接404
        if (!Request::has('spec_id')) {
            abort(404);
        }

        $spec = Models\Spec::find(Request::input('spec_id'));
        if (!$spec) {
            abort(404);
        }

        $this->crud->addField([
            'name'      => 'spec_id',
            'type'      => 'hidden',
            'value'     => Request::input('spec_id'),
        ]);
        $this->crud->addField([
            'name'      => 'spec_name',
            'label'     => '所属规格',
            'type'      => 'text',
            'value'     => $spec->name,
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);
        $this->crud->addField([
            'name'       => 'name',
            'label'      => '值名称',
            'type'       => 'text',
        ]);
        $this->crud->addField([
            'name'       => 'sort_order',
            'label'      => '排序',
            'type'       => 'text',
            'default'    => '0',
        ]);
        
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->getSaveAction();

        return view($this->crud->getCreateView(), $this->data);
    }

    public function store()
    {
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/mall/spec-value?spec_id='.Request::input('spec_id'));
        return parent::storeCrud();
    }

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $this->data['entry'] = $this->crud->getEntry($id);

        $this->crud->addField([
            'name'      => 'spec_id',
            'type'      => 'hidden',
        ]);
        $this->crud->addField([
            'name'      => 'spec_name',
            'label'     => '所属规格',
            'type'      => 'text',
            'value'     => $this->data['entry']->spec->name,
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);
        $this->crud->addField([
            'name'      => 'name',
            'label'     => '值名称',
            'type'      => 'text',
        ]);
        $this->crud->addField([
            'name'      => 'sort_order',
            'label'     => '排序',
            'type'      => 'text',
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
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/mall/spec-value?spec_id='.Request::input('spec_id'));
        return parent::updateCrud();
    }
}
