<?php

namespace Modules\Oms\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Modules\Oms\Models;
use Validator;

class OrderCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("Modules\Oms\Models\Order");
        $this->crud->setEntityNameStrings('订单', '订单');
        $this->crud->setRoute(config('backpack.base.route_prefix', 'admin').'/mall/order');
        $this->crud->denyAccess(['create', 'delete']);
        //Column
        $this->crud->setColumns([
            [
                'name'          => 'id',
                'label'         => '订单ID',
            ],
            [
                'name'          => 'order_no',
                'label'         => '订单编号',
            ],
            [
                'name'          => 'pay_sn',
                'label'         => '支付单号',
            ],
            [
                'name'          => 'user_id',
                'label'         => '买家ID',
            ],
            [
                'name'          => 'user_name',
                'label'         => '买家账号',
            ],
            [
                'name'          => 'order_from',
                'label'         => '订单来源',
                'type'          => 'radio',
                'options'       => [
                    0           => 'PC',
                    10          => 'Android',
                    30          => 'IOS'
                ],
            ],
            [
                'name'          => 'created_at',
                'label'         => '下单时间',
            ],
            [
                'name'          => 'goods_amount',
                'label'         => '商品总价（元）',
            ],
            [
                'name'          => 'freight',
                'label'         => '运费（元）',
            ],
            [
                'name'          => 'coupon_info',
                'label'         => '优惠信息',
                'type'          => 'model_function',
                'function_name' => 'getCouponInfo',
            ],
            [
                'name'          => 'order_amount',
                'label'         => '订单金额（元）',
            ],
            [
                'name'          => 'payment_code',
                'label'         => '支付方式',
            ],
            [
                'name'          => 'payment_time',
                'label'         => '支付时间',
            ],
            [
                'name'          => 'waybill_no',
                'label'         => '运单号码',
            ],
            [
                'name'          => 'refund_amount',
                'label'         => '退款金额',
            ],
            [
                'name'          => 'finnshed_time',
                'label'         => '订单完成时间',
            ],
            [
                'name'          => 'evaluation_status',
                'label'         => '评价状态',
                'type'          => 'radio',
                'options'       => [
                    0           => '未评价',
                    10          => '已评价',
                    30          => '已过期未评价'
                ],
            ],
            [
                'name'          => 'order_status',
                'label'         => '订单状态',
                'type'          => 'radio',
                'options'       => [
                    0           => '已取消',
                    1           => '已锁定',
                    10          => '待付款',
                    20          => '待发货',
                    30          => '待收货',
                    40          => '已确认收货',
                    50          => '待退款',
                    60          => '退款成功'
                ],
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
        return parent::index();
    }

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $this->data['entry'] = $this->crud->getEntry($id);

        //操作
        $this->addFieldByStatus($this->data['entry']->order_status);

        //订单状态
        $this->crud->addField([
            'name'              => 'order_status',
            'label'             => '订单状态',
            'type'              => 'select2_from_array',
            'options'           => [
                0               => '已取消',
                1               => '已锁定',
                10              => '待付款',
                20              => '待发货',
                30              => '待收货',
                40              => '已确认收货',
                50              => '待退款',
                60              => '退款成功'
            ],
            'attributes'        => [
                'disabled'      => 'disabled'
            ],
        ]);

        //商品信息
        $items_tmp = Models\OrderGoods::select('goods_id', 'goods_image', 'goods_name', 'goods_price', 'goods_num')
                                        ->where('order_id', $this->data['entry']->id)
                                        ->get()
                                        ->toArray();
        foreach ($items_tmp as $key => $value) {
            $goods_id = $value['goods_id'];
            unset($value['goods_id']);
            $items[$goods_id] = $value;
        }
        $this->crud->addField([
            'name'              => 'info',
            'label'             => '商品信息',
            'type'              => 'table2',
            'columns'           => [
                '商品'          => 2,
                '单价'          => 1,
                '数量'          => 1,
            ],
            'value'             => $items,
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
        $curd_rs = parent::updateCrud();

        $order = $this->data['entry'];

        switch ($order->order_status) {
            case 10:
                $order->order_amount = $order->goods_amount + $order->freight - $order->expansion->promotion_amount;
                break;

            case 20:
                // 创建默认的验证器
                $validator = Validator::make([], []);
                $validator->after(function ($validator) use($order) {
                    if ($order->waybill_no == null || $order->waybill_no == '') {
                        $validator->errors()->add('waybill_no', '运单号码不能为空');
                    }
                });

                $this->validateWith($validator);

                $order->order_status = 30;
                break;

            default:
        }
        $order->save();

        return $curd_rs;
    }

    protected function addFieldByStatus($order_status)
    {
        switch ($order_status) {
            case 10:
                //修改商品总价
                $this->crud->addField([
                    'name'              => 'goods_amount',
                    'label'             => '商品总价',
                    'type'              => 'number',
                    'attributes'        => ["step" => "any"],
                    'prefix'            => '￥',
                    'wrapperAttributes' => [
                        'class'         => 'form-group col-md-6'
                    ],
                ]);
                //修改运费
                $this->crud->addField([
                    'name'              => 'freight',
                    'label'             => '运费',
                    'type'              => 'number',
                    'attributes'        => ["step" => "any"],
                    'prefix'            => '￥',
                    'wrapperAttributes' => [
                        'class'         => 'form-group col-md-6'
                    ],
                ]);
                break;

            case 20:
                //选择物流公司
                $this->crud->addField([
                    'name'              => 'logistics_company',
                    'label'             => '物流公司',
                    'type'              => 'select2_from_array',
                    'options'           => [
                        '顺丰速运'       => '顺丰速运',
                        '邮政EMS'       => '邮政EMS',
                        '圆通快递'       => '圆通快递',
                    ],
                    'wrapperAttributes' => [
                        'class'         => 'form-group col-md-6'
                    ],
                ]);
                //填写运单号码
                $this->crud->addField([
                    'name'              => 'waybill_no',
                    'label'             => '运单号码',
                    'type'              => 'number',
                    'wrapperAttributes' => [
                        'class'         => 'form-group col-md-6'
                    ],
                ]);

                break;

            case 30:
            case 40:
                //查看物流公司
                $this->crud->addField([
                    'name'              => 'logistics_company',
                    'label'             => '物流公司',
                    'type'              => 'text',
                    'wrapperAttributes' => [
                        'class'         => 'form-group col-md-6'
                    ],
                    'attributes'        => [
                        'disabled'      => 'disabled'
                    ],
                ]);
                //查看运单号码
                $this->crud->addField([
                    'name'              => 'waybill_no',
                    'label'             => '运单号码',
                    'type'              => 'number',
                    'wrapperAttributes' => [
                        'class'         => 'form-group col-md-6'
                    ],
                    'attributes'        => [
                        'disabled'      => 'disabled'
                    ],
                ]);
                break;

            default:
        }
    }
}
