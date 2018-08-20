<?php

Route::group(['prefix' => config('backpack.base.route_prefix', 'admin'), 'module' => 'mall'], function() {
    Route::group(['middleware' => 'admin'], function() {
        Route::get('/mall',             'Admin\HomeController@index');
        Route::resource('mall/setting', 'Admin\SettingCrudController', ['names' => [
            'index' => 'admin.mall.setting.index'
        ]]);
        Route::resource('mall/goods-spu', 'Admin\GoodsSpuCrudController', ['names' => [
            'index' => 'admin.mall.goods_spu.index',
            'edit' => 'admin.mall.goods_spu.edit',
        ]]);
        Route::resource('mall/goods', 'Admin\GoodsCrudController', ['names' => [
            'index' => 'admin.mall.goods.index',
            'create' => 'admin.mall.goods.create',
        ]]);
        Route::resource('mall/topic', 'Admin\TopicCrudController', ['names' => [
            'index' => 'admin.mall.topic.index'
        ]]);
        Route::resource('mall/brand', 'Admin\BrandCrudController', ['names' => [
            'index' => 'admin.mall.brand.index'
        ]]);
        Route::resource('mall/category', 'Admin\CategoryCrudController', ['names' => [
            'index' => 'admin.mall.category.index',
            'create' => 'admin.mall.category.create',
        ]]);
        Route::resource('mall/type', 'Admin\TypeCrudController', ['names' => [
            'index' => 'admin.mall.type.index'
        ]]);
        Route::resource('mall/spec', 'Admin\SpecCrudController', ['names' => [
            'index' => 'admin.mall.spec.index',
            'create' => 'admin.mall.spec.create',
        ]]);
        Route::resource('mall/spec-value', 'Admin\SpecValueCrudController', ['names' => [
            'index' => 'admin.mall.spec_value.index',
            'create' => 'admin.mall.spec_value.create',
        ]]);

        // 服务保障管理
        Route::resource('mall/service',             'Admin\ServiceCrudController', ['names' => [
            'index' => 'admin.mall.service.index',
            'create' => 'admin.mall.service.create',
        ]]);
    });
});
