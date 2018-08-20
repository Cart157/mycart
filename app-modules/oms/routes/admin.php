<?php

Route::group(['prefix' => config('backpack.base.route_prefix', 'admin'), 'module' => 'oms'], function() {
    Route::group(['middleware' => 'admin'], function() {
        Route::get('/oms',              'Admin\HomeController@index');
        Route::resource('oms/setting',  'Admin\SettingCrudController', ['names' => [
            'index' => 'admin.oms.setting.index'
        ]]);

        // 订单管理
        Route::resource('oms/order',        'Admin\OrderCrudController', ['names' => [
            'index' => 'admin.oms.order.index',
        ]]);

        // 收货人管理
        Route::resource('oms/consignee',    'Admin\ConsigneeCrudController', ['names' => [
            'index' => 'admin.oms.consignee.index',
            'create' => 'admin.oms.consignee.create',
        ]]);
    });
});
