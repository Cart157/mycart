<?php

namespace Modules\Base\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Modules\Mobile\Models\AppUpdateLog;
use Request;

class AppUpdateLogCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Base\Models\AppUpdateLog");
        $this->crud->setEntityNameStrings('App更新', 'App更新');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/app-update');

        // 类型过滤器
        $this->crud->addFilter(
            [
                'type' => 'dropdown',
                'name' => 'device_type',
                'label'=> '设备类型',
            ],
            [
                'android'   => 'android',
                'ios'       => 'ios',
            ],
            function($value) {
                $this->crud->addClause('where', 'device_type', $value);
            }
        );

        // 列表显示列
        $this->crud->setColumns([
            [
                'name'  => 'id',
                'label' => 'ID',
            ],
            [
                'name'  => 'device_type',
                'label' => '设备类型',
            ],
            [
                'name'  => 'version',
                'label' => '版本号',
            ],
            [
                'name'  => 'version_name',
                'label' => '版本名称',
            ],
            [
                'name'  => 'update_url',
                'label' => '更新地址',
            ],
            [
                'name'  => 'update_content',
                'label' => '更新内容',
            ],
            [
                'name'      => 'is_force',
                'label'     => '是否强制更新',
                'type'      => 'boolean',
                'options'   => [0 => '不强制', 1 => '强制'],
            ],
            [
                'name'      => 'is_ignore',
                'label'     => '可否忽略此版本',
                'type'      => 'boolean',
                'options'   => [0 => '不能忽略', 1 => '可忽略'],
            ],
        ]);

        // 编辑页字段
        $this->crud->addField([
            'name'          => 'device_type',
            'label'         => '设备类型',
        ]);

        $this->crud->addField([
            'name'          => 'version',
            'label'         => '版本号',
        ]);

        $this->crud->addField([
            'name'          => 'version_name',
            'label'         => '版本名称',
        ]);

        $this->crud->addField([
            'name'          => 'update_url',
            'label'         => '更新地址',
        ]);

        $this->crud->addField([
            'name'          => 'update_content',
            'label'         => '更新内容',
            'type'          => 'textarea',
            'attributes'    => [
                'rows' => '10',
            ],
        ]);

        $this->crud->addField([
            'name'        => 'is_force',
            'label'       => '是否强制更新',
            'type'        => 'radio',
            'options'     => [
                0 => '不强制',
                1 => '强制',
            ],
            'default'     => 0,
        ]);

        $this->crud->addField([
            'name'        => 'is_ignore',
            'label'       => '是否可忽略',
            'type'        => 'radio',
            'options'     => [
                1 => '可忽略',
                0 => '不可忽略',
            ],
            'default'     => 1,
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
        $this->crud->addClause('orderBy', 'id', 'desc');

        return parent::index();
    }

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        $device_type = in_array(Request::input('device_type'), ['ios', 'android']) ? Request::input('device_type') : 'android';

        $version = AppUpdateLog::where('device_type', $device_type)->max('version');

        // 对通用字段进行覆盖
        $this->crud->addField([
            'name'          => 'device_type',
            'label'         => '设备类型',
            'type'          => 'select_type_array',
            'options'     => [
                'android'   => 'android',
                'ios'       => 'ios',
            ],
            'default'       => $device_type,
        ]);

        $this->crud->addField([
            'name'          => 'version',
            'label'         => '版本号（↓↓自动推荐，老的 '. $device_type .' 最大版本为'. $version .'）',
            'default'       => $version + 1,
        ]);

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

        $this->crud->addField([
            'name'          => 'device_type',
            'label'         => '设备类型',
            'attributes'    => [
                'disabled' => 'disabled',
            ],
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
        Request::offsetUnset('device_type');

        return parent::updateCrud();
    }
}
