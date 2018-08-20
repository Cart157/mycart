<?php

// 身份认证（登录，注册，密码找回，重置）
Route::group(['prefix' => config('const.auth_prefix', '')], function () {
    Route::get('login',             'Uhome\AuthController@login')->name('user.auth.login');
    Route::post('login',            'Uhome\AuthController@postLogin')->name('user.auth.login.post');
    Route::post('qrlogin/query',    'Uhome\AuthController@qrLoginQuery');

    Route::get('register', function() {
        return view('base::uhome.auth.register');
    });
    // Route::get('/register',         'Uhome\AuthController@register')->name('user.auth.register');
    // Route::post('/register',        'Uhome\AuthController@postRegister')->name('user.auth.register.post');
    // Route::get('/logout',           'Uhome\AuthController@logout')->name('user.auth.logout');
    // Route::get('/find-password',    'Uhome\AuthController@findPassword')->name('user.auth.find_password');
    // Route::post('/find-password',   'Uhome\AuthController@postFindPassword')->name('user.auth.find_password.post');
});
