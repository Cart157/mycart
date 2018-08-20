<?php

namespace Modules\Mall\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Filesystem\Filesystem;
use Modules\Mall\Models;
use Request;
use Validator;
use DB;

class CategoryCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Mall\Models\Category");
        $this->crud->setEntityNameStrings('分类', '分类');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/mall/category');
        //Column
        $this->crud->setColumns([
            [
                'name'          => 'id',
                'label'         => 'ID',
            ],
            [
                'name'          => 'name',
                'label'         => '名称',
                'type'          => "model_function",
                'function_name' => 'getNameTreeHtml',
            ],
            [
                'name'          => 'cover_image',
                'label'         => '图片',
                'type'          => 'model_function',
                'function_name' => 'getCoverImageHtml',
            ],
            [
                'name'          => 'parent_id',
                'label'         => '父级分类',
                'type'          => 'select',
                'entity'        => 'parent',
                'attribute'     => 'name',
            ],
/*
            [
                'name'      => 'type_id',
                'label'     => '关联类型',
                'type'      => 'select',
                'entity'    => 'type',
                'attribute' => 'name',
            ],
 */

            [
                'name'      => 'sort_order',
                'label'     => '排序',
            ],
        ]);
        //Field
        $this->crud->addField([
            'name'      => 'name',
            'label'     => '名称',
            'type'      => 'text',
        ]);
        $this->crud->addField([
            'name'      => 'parent_id',
            'label'     => '父级分类',
            'type'       => 'select2_from_array',
            'options'    => Models\Category::treeOptions(),
        ]);
/*
        $this->crud->addField([
            'name'      => 'type_id',
            'label'     => '关联类型',
            'type'      => 'select2',
            'attribute' => 'name',
            'model'     => 'Modules\Mall\Models\Type',
        ]);
 */
        $this->crud->addField([
            'name'      => 'sort_order',
            'label'     => '排序',
            'type'      => 'text',
            'default'   => 0,
        ]);
        $this->crud->addField([
            'name'      => 'cover_image',
            'label'     => '封面图片',
            'type'      => 'upload',
            'upload'    => true,
        ]);
    }

    public function index()
    {
        $gc_tree_ids = Models\Category::treeIds();

        if (!empty($gc_tree_ids)) {
            $ids_ordered = implode(',', $gc_tree_ids);
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

        $this->data['entry']->path = '-'. implode('-', $this->data['entry']->getParents()) .'-';

        $category = $this->data['entry'];
        if ($category->cover_image) {
            $filesystem = new Filesystem();
            $target_dir = public_path().sprintf('/uploads/mall/category/%d', $category->id);

            if (!$filesystem->exists($target_dir)) {
                $filesystem->makeDirectory($target_dir, 0755, true);
            }

            $target_filename = basename($category->cover_image);
            $target_path = sprintf('/uploads/mall/category/%d/%s', $category->id, $target_filename);
            $filesystem->move(public_path().$category->cover_image, public_path().$target_path);

            $category->cover_image = $target_path;
        }
        $category->save();

        return $res;
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

        return view($this->crud->getEditView(), $this->data);
    }

    public function update()
    {
        // 验证：不能改父级
        $self = Models\Category::find(Request::input('id'));
        $parent = Models\Category::find(Request::input('parent_id'));

        // 创建默认的验证器
        $validator = Validator::make([], []);
        $validator->after(function ($validator) use($self, $parent) {
            if ($parent && starts_with($parent->path, $self->path)) {
                $validator->errors()->add('parent_id', '父级不能选成自己或自己的子节点');
            }
        });
        $this->validateWith($validator);

        $res = parent::updateCrud();

        // 更新自己的path
        $this->data['entry']->path = '-'. implode('-', $this->data['entry']->getParents()) .'-';
        $this->data['entry']->save();

        // 更新所有后代的path
        $old_path = $self->path;
        $offspring = Models\Category::where('path', 'like', $old_path.'%')->get();
        foreach ($offspring as $gc) {
            //$new_path = str_replace($old_path, $this->data['entry']->path, $ac->path);
            $gc->path = '-'. implode('-', $gc->getParents()) .'-';
            $gc->save();
        }

        return $res;
    }

    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        $gc = $this->crud->getEntry($id);
        if ($gc->children->count() > 0) {
            return response()->json(['message' => '要删除的项目有子节点，不能删除'], 500);
        }

        // todo:删除的分类里的文章，全部改为父级分类

        return $this->crud->delete($id);
    }
}