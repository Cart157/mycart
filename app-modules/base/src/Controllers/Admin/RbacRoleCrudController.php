<?php

namespace Modules\Base\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

use Modules\Base\Models;
use Request;
use Validator;
use DB;
use Illuminate\Validation\Rule;

class RbacRoleCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel('Modules\Base\Models\RbacRole');
        $this->crud->setEntityNameStrings('用户角色', '用户角色');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/rbac-role');

        $this->crud->setColumns([
            [
                'name'  => 'id',
                'label' => 'ID',
            ],
            [
                'name'  => 'name',
                'label' => '角色名称',
                'type'  => "model_function",
                'function_name' => 'getNameTreeHtml',
            ],
            [
                'name'  => 'slug',
                'label' => '固定名',
            ],
            [
                'name'  => 'parent_name',
                'label' => '父级角色名称',
                'type'      => 'select',
                'entity'    => 'parent',
                'attribute' => 'name',
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
                'name'       => 'name',
                'label'      => '角色名称',
                'type'       => 'text',
            ]);
            $this->crud->addField([
                'name'       => 'slug',
                'label'      => '固定名（请使用英文，修改前请确定没有在代码中使用）',
                'type'       => 'text',
            ]);
            $this->crud->addField([
                'name'       => 'parent_id',
                'label'      => '父级角色',
                'type'       => 'select2_from_array',
                'options'    => Models\RbacRole::treeOptions(),
            ]);
            $this->crud->addField([
                'name'       => 'remark',
                'label'      => '备注',
                'type'       => 'textarea',
            ]);

            $this->crud->addField([
                'name' => 'separator_permission',
                'type' => 'custom_html',
                'value' => '<br><br>++++++++++++++++++++++++++++++++++++++++ 角色绑定权限 ++++++++++++++++++++++++++++++++++++++++<br><br>',
            ]);

            // $permission_tree_arr = (new Models\RbacPermission)->getPermissionTree(true);
            $role_permissions = [];
            $id = collect(Request::segments())->filter(function ($value, $key) {
                return is_numeric($value);
            })->first();
            if ($id) {
                // 编辑的场合
                $role = Models\RbacRole::find($id);
                $role_permissions = $role->permissions->pluck('title', 'id')->all();
            }

            $permission_list = Models\RbacPermission::all();
            $permission_tree_arr = [];
            foreach ($permission_list as $permission) {
                $tmp_item = [
                    'id' => $permission->id,
                    'parent' => $permission->parent_id ?: '#',
                    'text'   => $permission->title,
                ];

                if (isset($role_permissions[$permission->id])) {
                    $tmp_item['state'] = ['selected' => true];
                }

                $permission_tree_arr[] = $tmp_item;
            }

            $this->crud->addField([
                'name'  => 'permission',
                'label' => '选择权限',
                'type'  => 'jstree',
                'tree_data' => $permission_tree_arr,
//                'value' => $permission_tree_arr,
            ]);
        }
    }

    public function index()
    {
        // $role_tree_ids = (new Models\RbacRole)->getRoleTree();
        $tree_ids = Models\RbacRole::treeIds();

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

        return view($this->crud->getCreateView(), $this->data);
    }

    public function store()
    {
        $table = (new Models\RbacRole() )->getTable();
        $validator = Validator::make(Request::all(),[
            'name' => 'required',
            'slug' => 'required|unique:'.$table.'slug'
        ]);
        $this->ValidateWith($validator);

        $res = parent::storeCrud();

        $this->data['entry']->path = '-'. implode('-', $this->data['entry']->getParents()) .'-';
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
        // 验证：不能改父级
        $self = Models\RbacRole::find(Request::input('id'));
        $parent = Models\RbacRole::find(Request::input('parent_id'));

        // 创建默认的验证器
        $table = (new Models\RbacRole() )->getTable();
        $validator = Validator::make(Request::all(), [
            'name'  =>  'required',
            'slug'  =>  ['required',Rule::unique($table)->ignore(Request::input('slug'))]
        ]);

        $validator->after(function ($validator) use($self, $parent) {
            if ($parent && starts_with($parent->path, $self->path)) {
                $validator->errors()->add('parent_id', '父级不能选成自己或自己的子节点');
            }
            if (Request::input('parent_id') != $self->parent_id && $self->permissions->count() > 0) {
                $validator->errors()->add('parent_id', '该角色已经绑定权限，不能再改变父级，原来的父级：' . $self->parent->name);
            }
        });

        $this->validateWith($validator);

        $res = parent::updateCrud();

        // 更新自己的path
        $this->data['entry']->path = '-'. implode('-', $this->data['entry']->getParents()) .'-';
        $this->data['entry']->save();

        // 更新所有后代的path
        $old_path = $self->path;
        $offspring = Models\RbacRole::where('path', 'like', "$old_path%")->get();
        foreach ($offspring as $role) {
            $role->path = '-'. implode('-', $role->getParents()) .'-';
            $role->save();
        }

        // 同步角色权限关系表
        $permission_arr = explode(',', Request::input('permission'));
        $self->permissions()->sync($permission_arr);

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
