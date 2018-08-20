<?php

Route::group(['prefix' => config('backpack.base.route_prefix', 'admin')], function() {
    Route::get('/logout',               'Admin\AuthController@logout');
    Route::group(['middleware' => 'admin.logined'], function() {
        Route::get('/login',            'Admin\AuthController@login');
        Route::post('/login',           'Admin\AuthController@postLogin');
    });

    Route::group(['middleware' => 'admin'], function() {
        Route::get('dashboard', 'Admin\HomeController@dashboard');
        Route::get('/', 'Admin\HomeController@redirect');

        Route::resource('setting',          'Admin\SettingCrudController');

        Route::resource('app-update',       'Admin\AppUpdateLogCrudController', ['names' => [
            'index' => 'admin.base.app_update_log.index'
        ]]);

        Route::resource('rbac-role',        'Admin\RbacRoleCrudController');
        Route::resource('rbac-permission',  'Admin\RbacPermissionCrudController');

        Route::resource('user',             'Admin\UserCrudController');
        Route::resource('user-banned',      'Admin\UserBannedCrudController');

        Route::resource('fun-type',         'Admin\FunTypeCrudController', ['names' => [
            'index' => 'admin.base.fun_type.index',
            'create' => 'admin.base.fun_type.create'
        ]]);
        Route::resource('banner',           'Admin\BannerCrudController', ['names' => [
            'index' => 'admin.base.banner.index',
            'create' => 'admin.base.banner.create'
        ]]);

        Route::resource('talent-apply',     'Admin\TalentApplyLogCrudController', ['names' => [
            'index' => 'admin.base.talent_apply.index',
            'create' => 'admin.base.talent_apply.create'
        ]]);
        Route::resource('talent',           'Admin\TalentCrudController', ['names' => [
            'index' => 'admin.base.talent.index',
            'create' => 'admin.base.talent.create'
        ]]);
        Route::resource('coin-grant',       'Admin\UserCoinLogCrudController', ['names' => [
            'index' => 'admin.base.coin_grant.index',
            'create' => 'admin.base.coin_grant.create'
        ]]);
    });
});

Route::post('payment/refund',   'Common\PayController@refund');
