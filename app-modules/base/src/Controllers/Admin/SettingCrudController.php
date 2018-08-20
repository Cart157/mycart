<?php

namespace Modules\Base\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Request;
use Validator;

class SettingCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Base\Models\Setting");
        $this->crud->setEntityNameStrings(trans('base::settings.setting_singular'), trans('base::settings.setting_plural'));
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/setting');
//        $this->crud->denyAccess(['create', 'delete']);
        $this->crud->setColumns([
            [
                'name'  => 'key',
                'label' => trans('base::settings.key'),
            ],
//            [
//                'name'  => 'name',
//                'label' => trans('base::settings.name'),
//            ],
            [
                'name'  => 'value',
                'label' => trans('base::settings.value'),
            ],
            [
                'name'  => 'memo',
                'label' => trans('base::settings.description'),
            ],
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
//        $this->crud->addClause('where', 'active', 1);

        return parent::index();
    }

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->getSaveAction();

        $this->crud->addField([
            'name'       => 'key',
            'label'      => trans('base::settings.key'),
            'type'       => 'text',
        ]);
        $this->crud->addField([
            'name'       => 'value',
            'label'      => trans('base::settings.value'),
            'type'       => 'textarea',
        ]);
        $this->crud->addField([
            'name'       => 'memo',
            'label'      => trans('base::settings.description'),
            'type'       => 'textarea',
        ]);

        return view($this->crud->getCreateView(), $this->data);
    }

    public function store()
    {

        $validator  = Validator::make(Request::all(),[
            'key'   => 'required',
            'value' => 'required',
            'memo'  => 'nullable'
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
        //$this->crud->addField((array) json_decode($this->data['entry']->field)); // <---- this is where it's different
        $this->crud->addField([
            'name'       => 'key',
            'label'      => trans('base::settings.key'),
            'type'       => 'text',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);
        $this->crud->addField([
            'name'       => 'value',
            'label'      => trans('base::settings.value'),
            'type'       => 'textarea',
        ]);
        $this->crud->addField([
            'name'       => 'memo',
            'label'      => trans('base::settings.description'),
            'type'       => 'textarea',
        ]);

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
        $validator = Validator::make(Request::all(),[
            'value' => 'required',
            'memo'  => 'nullable'
        ]);
        $this->validateWith($validator);

        return parent::updateCrud();
    }
}
