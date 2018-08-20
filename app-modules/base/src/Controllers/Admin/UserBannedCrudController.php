<?php

namespace Modules\Base\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Request;

class UserBannedCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Base\Models\UserBanned");
        $this->crud->setEntityNameStrings('封禁用户', '封禁用户');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/user-banned');

        $this->crud->setColumns([
            [
                'name'      => 'id',
                'label'     => 'ID',
            ],
            [
                'name'      => 'user_id',
                'label'     => '用户ID',
            ],
            [
                'name'      => 'user_name',
                'label'     => '用户昵称',
                'type'      => 'select',
                'entity'    => 'user',
                'attribute' => 'name',
            ],
            [
                'name'      => 'reason',
                'label'     => '封禁原因',
            ],
        ]);

        $this->crud->addField([
            'name'          => 'user_id',
            'label'         => '用户ID',
            'type'          => 'number',
        ]);
        $this->crud->addField([
            'name'          => 'reason',
            'label'         => '封禁原因',
            'type'          => 'text',
        ]);
    }

    public function index()
    {
        if (Request::has('wd')) {
            $this->crud->addClause('whereHas', 'user', function ($query) {
                $query->where('name', 'like', '%'.Request::input('wd').'%');
            });
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
        return parent::updateCrud();
    }
}
