<?php

Route::post('/captcha/get',             'Common\CaptchaController@apiGet');
Route::post('/common/image/upload',     'Common\ImageController@postUpload');
Route::post('/common/image/eleditor-upload',     'Common\ImageController@postEleditorUpload');

Route::get('/captcha/sms',              'Common\CaptchaController@sms');
Route::get('/captcha/sms-app',          'Common\CaptchaController@smsApp');
Route::get('/captcha/image',            'Common\CaptchaController@image');
Route::get('/captcha/lyric',            'Common\CaptchaController@lyric');


/**
 * 支付（异步通知，回调）
 * 全应用通用接口，写在base里
 * 重要：以后把 api前缀 去掉
 * @METHOD /api/payment/{mode}/{acttion}
 */
Route::post('api/payment/alipay/notify',                'Common\Payment\AlipayController@notify');
Route::get('api/payment/alipay/return',                 'Common\Payment\AlipayController@return');
Route::post('api/payment/wechat/notify',                'Common\Payment\WechatController@notify');

Route::post('netease/im/notify',                        'Common\Netease\ImController@notify');
