<?php

// TODO: 合并进'mall/goods'
Route::get('mall/goods/search',                 'Api\GoodsController@search');
Route::apiResource('mall/brand',                'Api\BrandController');
Route::apiResource('mall/category',             'Api\CategoryController');
Route::apiResource('mall/goods',                'Api\GoodsController');
Route::apiResource('mall/goods-spu',            'Api\GoodsSpuController');

/**
 * 商品分享
 */
Route::put('mall/goods/{id}/share',             'Api\GoodsController@share');

// /**
//  * 商城优惠券（列表）
//  * @METHOD /api/mall/goods/evaluation
//  */
// Route::get('mall/promotion/coupon',             'Api\PromotionCouponController@index');
// Route::get('mall/promotion/coupon/{id}',        'Api\PromotionCouponController@show');

// /**
//  * 商品评价（列表）
//  * @METHOD /api/mall/goods/evaluation
//  */
// Route::get('mall/goods-evaluation',             'Api\EvaluationController@index');

/**
 * 专题（列表、详细）
 * @METHOD /api/mall/topic
 */
Route::get('mall/topic',                        'Api\TopicController@index');
Route::get('mall/topic/{id}',                   'Api\TopicController@show');

// 云ID
Route::get('mall/cloud-brand',                  'Api\BrandController@cloudBrand');
Route::get('mall/brand/{id}/cloud-series',      'Api\BrandController@cloudSeries');

// // 抽奖信息
// Route::get('mall/promotion/prize',              'Api\PromotionPrizeController@index');

// // 物流费用信息
// // Route::get('mall/logistics/fee',                 'Api\LogisticsController@fee');
// Route::get('mall/logistics/query',              'Api\LogisticsController@query');


// 需要登录才可访问
Route::group(['middleware' => 'jwt'], function () {
    // // 购物车: hack uri
    // // TODO: 计划删除
    // Route::get('mall/cart/add',                 'Api\CartController@store');
    // Route::get('mall/cart/edit',                'Api\CartController@batchUpdate');
    // Route::get('mall/cart/delete',              'Api\CartController@batchDestroy');
    // 购物车: 正式api
    Route::get('mall/cart/count',               'Api\CartController@count');
    Route::post('mall/cart/checkout',           'Api\CartController@checkout');
    Route::put('mall/cart',                     'Api\CartController@batchUpdate');
    Route::delete('mall/cart',                  'Api\CartController@batchDestroy');
    Route::apiResource('mall/cart',             'Api\CartController', ['only' => [
        'index', 'store'
    ]]);

//     // i删除订单
//     Route::put('mall/order/del',                'Api\OrderController@softdel');
//     Route::post('mall/order/checkout',          'Api\OrderController@checkout');
//     Route::post('mall/order/calculate',         'Api\OrderController@calculate');

//     // 订单（列表，详细，添加，修改）
//     Route::get('mall/order/add',                'Api\OrderController@store');
//     Route::apiResource('mall/order',            'Api\OrderController', ['only' => [
//         'index', 'store', 'show', 'update'
//     ]]);

//     //提交 私人订制订单
// //    Route::put('mall/order/{id}',               'Api\OrderController@update');
//     Route::post('mall/custom-order',            'Api\OrderController@storeCustom');


//     //订单状态跟踪 id为订单号  展示订单状态列表
//     Route::get('mall/order/{id}/flows',         'Api\OrderController@flows');


//     //订单修改---修改！目标！！！！！！！！！！！     提交物流信息
//     Route::put('mall/order/{id}/waybill-no',    'Api\OrderController@updateWaybillNo');

//     Route::get('mall/order/{id}/platform-consignee',        'Api\OrderController@platformConsignee');
//     Route::get('mall/order/{id}/servicer-consignee',        'Api\OrderController@servicerConsignee');


    // /**
    //  * 商品评价（发布评价）
    //  * @METHOD /api/mall/goods/evaluation
    //  */
    // Route::post('mall/goods-evaluation',        'Api\EvaluationController@store');

    // //定制订单评价
    // Route::post('mall/custom-evaluation',        'Api\EvaluationController@customStore');

    // // 申请售后、查看售后信息
    // Route::post('mall/order/{id}/refund',       'Api\RefundController@store');
    // Route::get('mall/order/{id}/refund',        'Api\RefundController@index');

    // // 收货地址（列表、添加、修改）
    // // Route::get('mall/consignee/add',            'Api\ConsigneeController@store');
    // Route::apiResource('mall/consignee',        'Api\ConsigneeController', ['only' => [
    //     'index', 'store', 'update', 'destroy'
    // ]]);

    // // 用户优惠券（列表、添加[兑换/购买/领取]、详情）
    // Route::apiResource('mall/user-coupon',      'Api\UserCouponController', ['only' => [
    //     'index', 'store', 'show'
    // ]]);

    // //统计用户优惠券的数量
    // Route::get('user/couponnumber',             'Api\UserCouponController@userCouponCount');

    // // 售后（列表、添加、详情）
    // Route::apiResource('mall/after-sale',       'Api\UserCouponController', ['only' => [
    //     'index', 'store', 'show'
    // ]]);

    // /**
    //  * 金币兑换商城
    //  * @METHOD /api/mall/goods/evaluation
    //  */
    // Route::get('mall/exchange',                 'Api\ExchangeController@index');
    // Route::get('mall/exchange/{id}',            'Api\ExchangeController@show');
    // Route::post('mall/exchange/{id}/exchange',  'Api\ExchangeController@exchange');
    // Route::get('mall/exchange/{id}/exchange',   'Api\ExchangeController@exchange');
    // Route::get('mall/user-exchange-log',        'Api\UserExchangeLogController@index');

    // // 抽奖
    // Route::post('mall/promotion/prize',         'Api\PromotionPrizeController@store');
});
