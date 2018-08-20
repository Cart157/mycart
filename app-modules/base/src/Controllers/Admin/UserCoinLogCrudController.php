<?php

namespace Modules\Base\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Modules\Base\Models;
use Modules\Activity\Models as ActivityModels;
use Request;
use Validator;
use DB;

class UserCoinLogCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Base\Models\UserCoinLog");
        $this->crud->setEntityNameStrings('金币发放', '金币发放');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/coin-grant');

        $this->crud->denyAccess(['update', 'delete']);

        $this->crud->setColumns([
            [
                'name'  => 'id',
                'label' => 'ID',
            ],
            [
                'name'  => 'user_id',
                'label' => '用户ID',
            ],
            [
                'name'  => 'user_name',
                'label' => '用户名',
                'type'      => 'select',
                'entity'    => 'user',
                'attribute' => 'name',
            ],
            [
                'name'  => 'change_num',
                'label' => '金币发放数',
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
        $this->crud->addClause('where', 'get_way_id', 9);   // 9为手动调整

        return parent::index();
    }

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->getSaveAction();

        $this->crud->addField([
            'name'       => 'activity_id',
            'label'      => '活动ID',
            'type'       => 'text',
        ]);
        $this->crud->addField([
            'name'       => 'user_id',
            'label'      => '用户ID',
            'type'       => 'text',
        ]);
        $this->crud->addField([
            'name'       => 'change_num',
            'label'      => '发放数量',
            'type'       => 'text',
        ]);
        $this->crud->addField([
            'name'       => 'memo',
            'label'      => '备注',
            'type'       => 'textarea',
            'default'    => '活动得奖',
            'attributes' => [
                'placeholder' => '活动得奖，或其他原因',
            ],
        ]);

        return view($this->crud->getCreateView(), $this->data);
    }

    public function store()
    {
        try {
            $validator = Validator::make(Request::all(), [
                'activity_id'   => 'required|integer|min:1',
                'user_id'       => 'required|integer|min:1',
                'change_num'    => 'required|integer|min:1',
                'memo'          => 'nullable|min:2',
            ], [
                'activity_id.required'  => '活动ID必须填写',
                'activity_id.integer'   => '活动ID只能是整数',
                'activity_id.min'       => '活动ID不存在',
                'user_id.required'  => '用户ID必须填写',
                'user_id.integer'   => '用户ID只能是整数',
                'user_id.min'       => '用户ID不存在',
                'change_num.required'=> '发放数量必须填写',
                'change_num.integer'=> '只能发放整数个金币',
                'change_num.min'    => '别闹，最少发1个金币',
                'memo.min'          => '备注最少写两个字',
            ]);

            $activity = ActivityModels\Activity::find(Request::input('activity_id'));
            $validator->after(function ($validator) use($activity) {
                if (!$activity) {
                    $validator->errors()->add('activity_id', '该活动不存在');
                }
            });

            $user = Models\User::find(Request::input('user_id'));
            $validator->after(function ($validator) use($user) {
                if (!$user) {
                    $validator->errors()->add('user_id', '该用户不存在');
                }
            });

            $this->validateWith($validator);

            DB::beginTransaction();

            Request::merge([
                'get_way_id' => 9,  // 9为手动调整
                'memo'       => Request::input('memo') . ' -> ' . $activity->name,
            ]);
            $user->profile->coin_num += Request::input('change_num');
            $user->profile->save();

            $curd_rs = parent::storeCrud();

            system_notice($user->id, [
                'type'              => 'ext-activity-draw',
                'ext_activity_id'   => $activity->id,
                'message'           => '发放了 '. Request::input('change_num') .' 个得奖金币',
            ], Models\User::find(176));

            DB::commit();
            return $curd_rs;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
