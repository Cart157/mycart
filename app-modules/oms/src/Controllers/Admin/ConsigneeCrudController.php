<?php

namespace Modules\Oms\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Request;

class ConsigneeCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Oms\Models\Consignee");
        $this->crud->setEntityNameStrings('收货人', '收货人');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/oms/consignee');

        $this->crud->denyAccess(['create', 'update', 'delete']);

        //Column
        $this->crud->setColumns([
            [
                'name'      => 'id',
                'label'     => 'ID',
            ],
            [
                'name'      => 'user_name',
                'label'     => '用户名',
                'type'      => 'select',
                'entity'    => 'user',
                'attribute' => 'name',
            ],
            [
                'name'      => 'consignee_name',
                'label'     => '收货人姓名',
            ],
            [
                'name'      => 'area_code',
                'label'     => '地区码',
            ],
            [
                'name'      => 'area_info',
                'label'     => '省市区',
            ],
            [
                'name'      => 'address',
                'label'     => '详细地址',
            ],
            [
                'name'      => 'tel_phone',
                'label'     => '电话',
            ],
            [
                'name'      => 'mb_phone',
                'label'     => '手机号',
            ],
            [
                'name'      => 'zip_code',
                'label'     => '邮编',
            ],
            [
                'name'      => 'is_default',
                'label'     => '是否为默认地址',
                'type'       => 'boolean',
                'options'    => [0 => '否', 1 => '是'],
            ],
        ]);
        //Field
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
    }

    public function index()
    {
        if (Request::has('wd')) {
            $this->crud->addClause('whereRaw', "CONCAT(IFNULL(base_user.name,''),IFNULL(consignee_name,''),IFNULL(area_info,''),IFNULL(address,''),IFNULL(mb_phone,'')) like ?", ['%'.Request::input('wd').'%']);
            $this->crud->addClause('leftJoin', 'base_user', 'user_id', '=', 'base_user.id');
            $this->crud->addClause('orderBy', 'user_id');
            $this->crud->addClause('orderBy', 'is_default', 'desc');
        }

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
