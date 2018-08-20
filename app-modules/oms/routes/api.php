<?php

// 物流费用信息
// Route::get('oms/logistics/fee',                 'Api\LogisticsController@fee');
Route::get('oms/logistics/query',               'Api\LogisticsController@query');

/**
 * 商品评价（列表）
 * @METHOD /api/mall/goods/evaluation
 */
Route::get('oms/evaluation',                    'Api\EvaluationController@index');

// 需要登录才可访问
Route::group(['middleware' => 'jwt'], function () {
    // =============================================
    // 订单
    // =============================================
    Route::get('oms/order',                     'Api\OrderController@index');
    Route::post('oms/order',                    'Api\OrderController@store');
    Route::get('oms/order/{id}',                'Api\OrderController@show');
    Route::put('oms/order/{id}',                'Api\OrderController@update');
    // Route::delete('oms/order/{id}',             'Api\OrderController@destroy');

    // 订单结算前的数据准备
    Route::post('oms/order/checkout',           'Api\OrderController@checkout');
    // 订单使用各种优惠时计算价格
    Route::post('oms/order/calculate',          'Api\OrderController@calculate');


    // 订单流，状态跟踪
    Route::get('oms/order/{id}/flows',          'Api\OrderController@flows');


    // 修改订单运单号
    Route::put('oms/order/{id}/waybill-no',     'Api\OrderController@updateWaybillNo');


    // =============================================
    // 收货地址
    // =============================================
    // 收货地址（列表、添加、修改）
    Route::get('oms/consignee',                 'Api\ConsigneeController@index');
    Route::post('oms/consignee',                'Api\ConsigneeController@store');
    Route::put('oms/consignee/{id}',            'Api\ConsigneeController@update');
    Route::delete('oms/consignee/{id}',         'Api\ConsigneeController@destroy');

    // 取得订单的平台和服务商收货地址
    Route::get('oms/order/{id}/platform-consignee',        'Api\OrderController@platformConsignee');
    Route::get('oms/order/{id}/servicer-consignee',        'Api\OrderController@servicerConsignee');

    /**
     * 商品评价（发布评价）
     * @METHOD /api/mall/goods/evaluation
     */
    Route::post('oms/evaluation',               'Api\EvaluationController@store');

    // //定制订单评价
    // Route::post('mall/custom-evaluation',        'Api\EvaluationController@customStore');


    // =============================================
    // 售后
    // =============================================
    // 申请售后、查看售后信息
    Route::post('oms/order/{id}/refund',        'Api\RefundController@store');
    Route::get('oms/order/{id}/refund',         'Api\RefundController@index');
});
