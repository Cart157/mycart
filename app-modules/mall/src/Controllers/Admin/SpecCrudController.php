<?php

namespace Modules\Mall\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Modules\Mall\Models;
use Request;

class SpecCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Mall\Models\Spec");
        $this->crud->setEntityNameStrings('规格', '规格');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/mall/spec');
        
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
                'name'          => 'tech_count',
                'label'         => '规格值的个数',
                'type'          => 'model_function',
                'function_name' => 'getValueCountHtml',
            ],
            [
                'name'          => 'spec_values',
                'label'         => '规格的值',
                'type'          => 'select_multiple',
                'entity'        => 'values',
                'attribute'     => 'name',
                'model'         => 'Modules\Mall\Models\SpecValue',
            ],
            [
                'name'          => 'sort_order',
                'label'         => '排序',
            ],
        ]);
    }
    
    public function index()
    {
        $this->crud->addButtonFromModelFunction('top', 'spec_value', 'btnManageSpecValue');

        $this->crud->addClause('orderBy', 'sort_order', 'asc');
        return parent::index();
    }
    
    public function create()
    {
        $this->crud->hasAccessOrFail('create');
        
        $this->crud->addField([
            'name'      => 'name',
            'label'     => '名称',
            'type'      => 'text',
        ]);
        $this->crud->addField([
            'name'      => 'sort_order',
            'label'     => '排序',
            'type'      => 'text',
            'default'   => 0,
        ]);
        
        
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->getSaveAction();
        
        return view($this->crud->getCreateView(), $this->data);
    }
    
    public function store()
    {
        return parent::storeCrud();
    }
    
    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');
        
        $this->data['entry'] = $this->crud->getEntry($id);
        
        $this->crud->addField([
            'name'      => 'name',
            'label'     => '名称',
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
        return parent::updateCrud();
    }
}
