<?php

namespace Modules\Base\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Request;
use Auth;

class OperationLogCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Base\Models\OperationLog");
        $this->crud->setEntityNameStrings('操作日志', '操作日志');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/product/operation-log');
        $this->crud->denyAccess(['create', 'update', 'delete']);
        $this->crud->allowAccess(['show']);

        $this->crud->setColumns([
            [
                'name'  => 'id',
                'label' => 'ID',
            ],
            [
                // 'name'  => 'user_id',
                // 'label' => '用户ID',
                'name'  => 'user_name',
                'label' => '用户',
                'type'      => 'select',
                'entity'    => 'user',
                'attribute' => 'name',
            ],
            [
                // 'name'  => 'method',
                // 'label' => '请求方法',
                'name'  => 'op_method',
                'label' => '操作方式',
                'type'  => 'model_function',
                'function_name' => 'getOpMethod',
            ],
            [
                // 'name'  => 'method',
                // 'label' => '请求方法',
                'name'  => 'op_entity',
                'label' => '操作实体',
                'type'  => 'model_function',
                'function_name' => 'getOpEntity',
            ],
            [
                'name'  => 'path',
                'label' => '操作路径',
            ],
            [
                'name'  => 'ip',
                'label' => '用户IP',
            ],
            [
                'name'  => 'input',
                'label' => '请求数据',
            ],
            [
                'name'  => 'created_at',
                'label' => '操作时间',
            ],
        ]);
    }

    public function index()
    {
        if (Request::has('wd')) {
            $this->crud->addClause('whereRaw', "CONCAT(IFNULL(name,''),IFNULL(path,''),IFNULL(input,''),IFNULL(ip,''),IF(method='POST','新增',''),IF(method='PUT','编辑',''),IF(method='DELETE','删除','')) like ?", ['%'.Request::input('wd').'%']);
            $this->crud->addClause('leftJoin', 'base_user', 'base_user.id', '=', 'base_operation_log.user_id');
        }

        if (!Auth::user()->roleIs('admin')) {
            $this->crud->addClause('where', 'path', 'like', 'admin/product/%');
            $this->crud->addClause('where', 'path', 'not like', 'admin/product/setting%');
        }

        $this->crud->addClause('orderBy', 'base_operation_log.id', 'desc');

        return parent::index();
    }

    public function show($id)
    {
        $this->crud->hasAccessOrFail('show');

        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['title'] = trans('backpack::crud.preview').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        $this->crud->setShowView('base::admin.operation_log.show');
        return view($this->crud->getShowView(), $this->data);
    }
}
