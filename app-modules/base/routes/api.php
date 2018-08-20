<?php

//腾讯云签名
Route::get('tencent/signature', 'Api\TencentController@signature');

//图片同步
Route::post('validate/image/sync', 'Api\AliValidateController@imageSync');
//图片异步
Route::post('validate/image/async', 'Api\AliValidateController@imageAsync');
//视频异步
Route::post('validate/video/async', 'Api\AliValidateController@videoAsync');

//添加用户备注标签
Route::post('user/{id}/note',                        'Api\UserController@note');
/**
 * 注册登陆（注册，登陆，检查手机注册，重设密码，刷新token）
 * @METHOD /api/*
 */
Route::post('auth/login',                   'Api\AuthController@login');
Route::post('auth/register',                'Api\AuthController@register');
Route::post('auth/check-mobile',            'Api\AuthController@checkMobile');
Route::post('auth/reset-password',          'Api\AuthController@resetPassword');
Route::post('auth/refresh-token',           'Api\AuthController@refreshToken');
Route::post('auth/socialite',               'Api\AuthController@socialite');
Route::post('auth/bind-mobile',             'Api\AuthController@bindMobile');
// Route::post('auth/wx-miniapp',              'Api\AuthController@wxMiniapp');
Route::post('auth/qr-login',                'Api\AuthController@qrLogin');
Route::post('auth/qr-login-confirm',        'Api\AuthController@qrLoginConfirm');


// 用户邀请注册
Route::get('user/inviter',                                  'Api\UserController@inviter');
Route::post('user/invitation',                              'Api\UserController@invitation');

# 扫描二维码，根据用户的唯一的序列号查询用户id
Route::post('user/qrcode/query',                            'Api\UserController@queryQrCode');

/**
 * 阿里云
 * @METHOD /api/*
 */
//通用（短信验证码）
Route::get('captcha/sms',                                   'Api\CaptchaController@sms');
//通用 （短信国家前缀号）
Route::get('captcha/sms/code',                              'Api\CaptchaController@code');

/**
 * 通用（省市区3级地址查询）
 * @METHOD /api/*
 */
Route::get('location',                                      'Api\LocationController@index');
Route::get('location/{code}',                               'Api\LocationController@show');


// 任务
Route::get('user/task',                                     'Api\TaskController@index');


/**
 * 标签（列表）
 * @METHOD /api/base/tag
 */
Route::get('base/tag',                                      'Api\TagController@index');
Route::get('base/web-tag',                                  'Api\TagController@webIndex');
//首页banner图片
Route::get('base/banner',                                   'Api\BannerController@index');

/**
 * app自动更新检查（详细）
 * @METHOD /api/mobile/app-update
 */
Route::get('app-update/{device_type}',                      'Api\AppUpdateController@current');

//七牛搜索图片
Route::post('cloud-storage/qiniu/image/resemble',                 'Api\CloudStorage\QiniuController@imageResemble');

// 需要登录才可访问
Route::group(['middleware' => 'jwt'], function () {
    // 扫码登录（前提是app已登录）
    Route::post('auth/qrlogin/scan',                        'Api\AuthController@qrLoginScan');
    Route::post('auth/qrlogin/confirm',                     'Api\AuthController@qrLoginConfirm');

    // 用户活动统计、文章、关注、粉丝
    Route::get('user/{id}/statistics',                      'Api\UserController@statistics');
    //Route::get('user/{id}/moment',              'Api\UserController@moment');
    Route::get('user/{id}/article',                         'Api\UserController@article');
    //Route::get('user/{id}/follow-topic',        'Api\UserController@followTopic');
    Route::get('user/{id}/follow-user',                     'Api\UserController@followUser');
    Route::get('user/{id}/follow-fans',                     'Api\UserController@followFans');

    /**
     * 用户（取得关注状态，更改关注状态）
     * @METHOD /api/circle/topic
     */
    Route::get('user/{id}/follow',                          'Api\UserController@followStatus');
    Route::put('user/{id}/follow',                          'Api\UserController@follow');

    // 用户资料取得和设置
    // TODO: 废止'user/setting'
    // Route::put('user/setting',                  'Api\UserController@setting');
    Route::get('user/{id}',                                 'Api\UserController@show');
    Route::put('user/{id}',                                 'Api\UserController@update');
    //绑定手机
    Route::put('user/{id}/bind-mobile',                     'Api\UserController@bindMobile');
    //绑定支付宝
    Route::put('user/{id}/bind-alipay',                     'Api\UserController@bindAlipay');
    //绑定
    Route::put('user/{id}/bind-socialite',                  'Api\UserController@bindSocialite');

    // 用户收藏夹
    Route::apiResource('user/{id}/favorite',                'Api\FavoriteController', ['only' => [
        'index', 'store'
    ]]);

    // 举报
    Route::post('user/{id}/report',                         'Api\ReportController@store');

    // 好友列表
    Route::get('user/{id}/friend',                          'Api\UserController@friend');

    // 用户列表
    Route::get('user',                                      'Api\UserController@index');


    /**
     * 素材管理
     */
    //列表
    Route::get('user/material',                             'Api\MaterialController@index');
    Route::post('user/material',                            'Api\MaterialController@store');
    Route::delete('user/material',                     'Api\MaterialController@destroy');

    /**
     * 支付（提交）
     * 全应用通用接口，写在base里
     * @METHOD /api/payment/pay
     */
    Route::post('payment/pay',                              'Api\PayController@pay');

    /**
     * 云存储（七牛）
     * 全应用通用接口，写在base里
     * @METHOD /api/cloud-storage/qiniu/{acttion}
     */
    Route::get('cloud-storage/qiniu/token',                 'Api\CloudStorage\QiniuController@token');

    /**
     * 系统通知（网易云）
     * 全应用通用接口，写在base里
     * @METHOD /api/system-notice
     */
    Route::get('system-notice',                             'Api\SystemNotificationController@index');
    Route::get('system-notice2',                            'Api\SystemNotificationController@index2');
    Route::delete('system-notice/{id}',                     'Api\SystemNotificationController@destroy');

    /**
     * 服务通知
     * 全应用通用接口，写在base里
     * @METHOD /api/service-notification
         */
    Route::get('service-notification',                      'Api\ServiceNotificationController@index');

    /**
     * 客服（网易云）
     * 全应用通用接口，写在base里
     * @METHOD /api/online-staff
     */
    Route::get('ban-staff',                                 'Api\BanStaffController@index');
    Route::post('ban-staff/pull',                           'Api\BanStaffController@pull');
    Route::post('ban-staff/join',                           'Api\BanStaffController@join');

    // 签到
    Route::get('user/{id}/checkin',                         'Api\CheckInController@show');
    Route::post('user/{id}/checkin',                        'Api\CheckInController@store');

    // 金币明细
    Route::get('user/coin-detail',                          'Api\UserCoinLogController@index');

    // 达人认证展示
    Route::get('user/talent',                               'Api\TalentController@show');
    // 申请达人认证
    Route::post('user/talent',                              'Api\TalentApplyLogController@store');
    //提现申请
    Route::post('user/cash',                                'Api\UserController@cashApply');



#-钱包相关-----------------------------------------------------------------------------------------------------
     //钱包首页
    Route::post('wallet/index',                             'Api\WalletController@walletIndex');

    //实名认证
    Route::post('user/truename',                            'Api\WalletController@checkTrueName');

    //现金余额流水
    Route::get('wallet/log',                                'Api\WalletController@walletLogDetail');

    //用户钱包余额
    Route::get('wallet/cash',                               'Api\WalletController@walletCashLeft');

    //获取用户手机号
    Route::get('user/mobile',                               'Api\WalletController@getUserMobile');

    //判断是否绑定了支付宝
    Route::get('wallet/havealipay',                         'Api\WalletController@haveAliPay');

    //绑定支付宝
    /*获取登录的token： /api/auth/login?mobile=13820693412&password=blueboy
    @URL get api/wallet/newalipay/{uid}?token=token值*/
    Route::post('wallet/newalipay',                         'Api\WalletController@bindAliPay');

    //设定和修改支付密码
    Route::post('wallet/password',                          'Api\WalletController@setPayPassword');
    #20180522
    Route::put('wallet/password',                           'Api\WalletController@reSetPayPassword');
    //存钱进入钱包
    Route::post('wallet/in',                                'Api\WalletController@saveMoney');
});

