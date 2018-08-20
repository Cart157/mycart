<?php

Route::group(['prefix' => 'store', 'middleware' => 'admin'], function() {
    //主页
    Route::get('/',                             'Store\HomeController@index');

    //商品
    Route::get('/goods',                        'Store\GoodsController@index');
    Route::put('/goods',                        'Store\GoodsController@batchUpdate');
    Route::delete('/goods',                     'Store\GoodsController@batchDelete');

    //商品垃圾箱（宝贝垃圾箱）
    Route::get('/goods-trash',                  'Store\GoodsTrashController@index');
    Route::put('/goods-trash',                  'Store\GoodsTrashController@batchUpdate');
    //Route::delete('/goods-trash',               'Store\GoodsTrashController@batchDelete');

    //商品发布
    Route::get('/goods/publish',                'Store\GoodsController@create');
    Route::post('/goods/publish',               'Store\GoodsController@store');

    //商品编辑
    Route::get('/goods/{id}/edit',              'Store\GoodsController@edit');
    Route::put('/goods/{id}/edit',              'Store\GoodsController@update');

    //商品（推荐、离线的）
    Route::get('/goods-recommend',              'Store\RecommendGoodsController@index');
    Route::put('/goods-recommend',              'Store\RecommendGoodsController@batchUpdate');
    Route::delete('/goods-recommend',           'Store\RecommendGoodsController@batchDelete');

    //商品（仓库中的、离线的）
    Route::get('/goods-offline',                'Store\GoodsOfflineController@index');
    Route::put('/goods-offline',                'Store\GoodsOfflineController@batchUpdate');
    Route::delete('/goods-offline',             'Store\GoodsOfflineController@batchDelete');

    //订单
    Route::get('/order',                        'Store\OrderController@index');
    Route::get('/order/{id}',                   'Store\OrderController@show');
    //修改订单（取消、改价）
    Route::put('/order/cancel/{id}',            'Store\OrderController@cancel');
    Route::put('/order/edit-price/{id}',        'Store\OrderController@editPrice');

    //物流
    Route::get('/logistics',                    'Store\OrderController@deliveryIndex');

    //发货信息
    Route::get('/logistics/delivery/{id}',      'Store\OrderController@deliveryInfo');
    //发货
    Route::put('/logistics/delivery/{id}',      'Store\OrderController@delivery');

    //评价
    Route::get('/evaluate',                     'Store\EvaluationController@index');
    Route::delete('/evaluate/{id}',             'Store\EvaluationController@delete');

    //宝贝分类
    Route::get('/brand',                        'Store\BrandController@index');


    // 售后
    Route::get('/refund',                       'Store\RefundController@index');
    Route::get('/refund/{id}/edit',             'Store\RefundController@show');

    Route::put('/refund/{id}/remark',           'Store\RefundController@updateRemark');
    Route::put('/refund/{id}/reject',           'Store\RefundController@reject');
    Route::put('/refund/{id}/agree-apply',      'Store\RefundController@agreeApply');

    Route::get('/refund/order/create',          'Store\RefundController@createOrder');
    Route::post('/refund/order',                'Store\RefundController@storeOrder');
});
