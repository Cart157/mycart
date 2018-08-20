<?php

namespace Modules\Base\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Modules\Base\Models;
use Request;
use Validator;

class TalentApplyLogCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Base\Models\TalentApplyLog");
        $this->crud->setEntityNameStrings('达人认证申请', '达人认证申请');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/talent-apply');
        $this->crud->denyAccess(['create', 'delete']);

        $this->crud->addFilter([
            'type'  => 'dropdown',
            'name'  => 'check_status',
            'label' => '审核状态'
        ],
        [
            0       => '已拒绝',
            1       => '已同意',
            10      => '待审核',
        ],
        function($value) {
            $this->crud->addClause('where', 'check_status', $value);
        });

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
                'label'         => '申请名称',
            ],
            [
                'name'          => 'certificate_image',
                'label'         => '辅助证明材料',
                'type'          => 'model_function',
                'function_name' => 'getImageHtml',
            ],
            [
                'name'          => 'description',
                'label'         => '叙述',
            ],
            [
                'name'          => 'check_status',
                'label'         => '审核状态',
                'type'          => 'radio',
                'options'       => [
                    0           => '已拒绝',
                    1           => '已同意',
                    10          => '待审核',
                ],
            ],
            [
                'name'          => 'reject_reason',
                'label'         => '拒绝理由',
            ],
        ]);
    }

    public function index()
    {
        if (Request::has('wd')) {
            $this->crud->addClause('where', 'name', 'like', '%'.Request::input('wd').'%');
        }
        $this->crud->addClause('orderBy', 'id', 'desc');
        return parent::index();
    }

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $this->data['entry'] = $this->crud->getEntry($id);

        $this->crud->addField([
            'name'          => 'user_id',
            'type'          => 'hidden',
        ]);
        $this->crud->addField([
            'name'          => 'user_name',
            'label'         => '用户昵称',
            'value'         => $this->data['entry']->user->name,
            'attributes'    => [
                'disabled'  => 'disabled',
            ],
        ]);
        $this->crud->addField([
            'name'          => 'name',
            'label'         => '申请名称',
            'type'          => 'text',
        ]);
        $this->crud->addField([
            'name'       => 'label',
            'type'       => 'custom_html',
            'value'      => '<label>辅助证明材料</label>',
        ]);
        $this->crud->addField([
            'name'       => 'image',
            'type'       => 'custom_html',
            'value'      => $this->imageHtml($id),
        ]);
        $this->crud->addField([
            'name'          => 'description',
            'label'         => '叙述',
            'type'          => 'textarea',
            'attributes'    => [
                'disabled'  => 'disabled',
            ],
        ]);
        $this->crud->addField([
            'name'          => 'check_status',
            'label'         => '审核',
            'type'          => 'radio',
            'options'       => [
                0           => '拒绝（请填写拒绝理由）',
                1           => '同意',
            ],
        ]);
        $this->crud->addField([
            'name'          => 'reject_reason',
            'label'         => '拒绝理由',
            'type'          => 'textarea',
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
        $crud_rs = parent::updateCrud();
        $log = Models\TalentApplyLog::find(Request::input('id'));
        $from_user = Models\User::find(178);

        if (Request::has('check_status') && Request::input('check_status') == 1) {
            $talent = Models\Talent::updateOrCreate([
                'user_id'       => $log->user_id,
            ],[
                'name'          => $log->name,
            ]);

            system_notice($log->user_id, [
                'type'      => 'official',
                'message'   => '您认证的"'.$log->name.'"审核已通过',
            ], $from_user);
        } elseif (Request::input('check_status') == 0) {

            $validator = Validator::make(Request::all(),[
                'reject_reason' => 'required'
            ]);
            
            $this->ValidateWith($validator);

            system_notice($log->user_id, [
                'type'      => 'official',
                'message'   => '您认证的"'.$log->name.'"审核未通过，原因：'.Request::input('reject_reason'),
            ], $from_user);
        }

        return $crud_rs;
    }

    public function imageHtml($id)
    {
        $html = '';
        $log = $this->crud->getEntry($id);
        if ($log->certificate_image) {
            $images = json_decode($log->certificate_image, true);
            foreach ($images as $value) {
                $html .= sprintf('<img src="%s" width="100%%">', cdn().$value);
            }
        }
        return $html;
    }
}
