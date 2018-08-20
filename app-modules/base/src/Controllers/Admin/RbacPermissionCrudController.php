<?php

namespace Modules\Base\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

use Modules\Base\Models;
use Request;
use Validator;
use DB;

class RbacPermissionCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel('Modules\Base\Models\RbacPermission');
        $this->crud->setEntityNameStrings('用户权限', '用户权限');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/rbac-permission');
        \Config::set('backpack.crud.default_page_length', 200);

        $this->crud->setColumns([
            [
                'name'  => 'id',
                'label' => 'ID',
            ],
            [
                'name'  => 'title',
                'label' => '权限标题',
                'type'  => "model_function",
                'function_name' => 'getTitleHtml',
            ],
            [
                'name'  => 'name',
                'label' => '权限名称（url）',
            ],
            [
                'name'  => 'remark',
                'label' => '备注',
            ],
        ]);

        $current_action = \Route::currentRouteAction();
        $current_action = substr(strstr($current_action, '@'), 1);

        if ($current_action != 'index') {
            $this->crud->addField([
                'name'       => 'title',
                'label'      => '权限标题',
                'type'       => 'text',
            ]);

            $this->crud->addField([
                'name'       => 'name',
                'label'      => '权限名称（url）',
                'type'       => 'text',
            ]);

            $permission_tree_ids = (new Models\RbacPermission)->getPermissionTree();
            if (!empty($permission_tree_ids)) {
                $ids_ordered = implode(',', $permission_tree_ids);
                $permission_list = Models\RbacPermission::orderByRaw(DB::raw("FIELD(id, $ids_ordered)"))->get();
            } else {
                $permission_list = [];
            }

            $permission_tree = ['无父级'];
            foreach ($permission_list as $permission) {
                $permission_tree[$permission->id] = $permission->getTitleHtml();
            }
            $this->crud->addField([
                'name'       => 'parent_id',
                'label'      => '父级角色',
                'type'       => 'select2_from_array',
                'options'    => $permission_tree,
            ]);
            $this->crud->addField([
                'name'       => 'remark',
                'label'      => '备注',
                'type'       => 'textarea',
            ]);
        }
    }

    public function index()
    {
        $permission_tree_ids = (new Models\RbacPermission)->getPermissionTree();

        if (!empty($permission_tree_ids)) {
            $ids_ordered = implode(',', $permission_tree_ids);
            $this->crud->addClause('orderByRaw', DB::raw("FIELD(id, $ids_ordered)"));
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
        $res = parent::storeCrud();

        if ($this->data['entry']->parent) {
            $this->data['entry']->path = $this->data['entry']->parent->path .'-'. $this->data['entry']->id;
        } else {
            $this->data['entry']->path = '$'. $this->data['entry']->id;
        }

        $this->data['entry']->save();

        return $res;
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

        return view($this->crud->getEditView(), $this->data);
    }

    public function update()
    {
        $self = Models\RbacPermission::find(Request::input('id'));
        $parent = Models\RbacPermission::find(Request::input('parent_id'));

        // 创建默认的验证器
        $validator = Validator::make([], []);
        $validator->after(function ($validator) use($self, $parent) {
            if ($parent && starts_with($parent->path, $self->path)) {
                $validator->errors()->add('parent_id', '父级不能选成自己或自己的子节点');
            }
        });
        $this->validateWith($validator);

        $old_path = $self->path;

        $res = parent::updateCrud();

        $this->data['entry']->path = $this->data['entry']->parent ? $this->data['entry']->parent->path .'-'. $this->data['entry']->id : '$'. $this->data['entry']->id;
        $this->data['entry']->save();

        $offspring = Models\RbacPermission::where('path', 'like', "$old_path%")->get();
        foreach ($offspring as $role) {
            $new_path = str_replace($old_path, $this->data['entry']->path, $role->path);
            $role->path = $new_path;
            $role->save();
        }

        return $res;
    }

    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        $role = $this->crud->getEntry($id);
        if ($role->children->count() > 0) {
            return response()->json(['message' => '要删除的项目有子节点，不能删除'], 500);
        }

        return $this->crud->delete($id);
    }
}
