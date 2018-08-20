<?php

namespace Modules\Base\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Request;
use Validator;

class TalentCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Base\Models\Talent");
        $this->crud->setEntityNameStrings('已认证达人', '已认证达人');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/talent');
        // $this->crud->denyAccess(['create', 'update']);

        $this->crud->setColumns([
            [
                'name'          => 'id',
                'label'         => 'ID',
            ],
            [
                'name'          => 'user_id',
                'label'         => '用户昵称',
                'type'          => 'select',
                'entity'        => 'user',
                'attribute'     => 'name',
            ],
            [
                'name'          => 'name',
                'label'         => '认证名称',
            ],
        ]);
    }

    public function index()
    {
        if (Request::has('wd')) {
/*
            $this->crud->addClause('whereHas', 'user', function ($query) {
                $query->where('name', 'like', '%'.Request::input('wd').'%');
            });
 */
            $this->crud->addClause('where', 'name', 'like', '%'.Request::input('wd').'%');
        }

        return parent::index();
    }

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->getSaveAction();

        $this->crud->addField([
            'name'       => 'user_id',
            'label'      => '用户ID',
            'type'       => 'text',
        ]);
        $this->crud->addField([
            'name'       => 'name',
            'label'      => '达人认证名称',
            'type'       => 'text',
        ]);

        return view($this->crud->getCreateView(), $this->data);
    }

    public function store()
    {
        $validator = Validator::make(Request::all(),[
            'user_id'   => 'required|integer|unique:base_talent',
            'name'      => 'required|max:20',
        ]);
        $this->validateWith($validator);

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
            'name'       => 'user_id',
            'label'      => '用户ID',
            'type'       => 'text',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);

        $this->crud->addField([
            'name'       => 'user_name',
            'label'      => '用户名',
            'type'       => 'text',
            'value'      => $this->data['entry']->user->name,
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);

        $this->crud->addField([
            'name'       => 'name',
            'label'      => '达人认证名称',
            'type'       => 'text',
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
        $validator = Validator::make(Request::all(),[
            'name'  => 'required|max:20',
        ]);
        $this->validateWith($validator);

        $input = Request::only(['name', 'id']);

        Request::replace($input);
        return parent::updateCrud();
    }
}
