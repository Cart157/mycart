<?php

namespace Modules\Base;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Modules\Base\Models\SiteSetting;
// use Modules\Cms\Models\Article;
// use Modules\Circle\Models\Moment;
// use Modules\Base\Libraries\Aliyuncs;
// use Modules\Base\Libraries\Baidu;
use App;
use Config;
use Schema;
use Route;
use Carbon\Carbon;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        // 路由全局约束
        Route::pattern('id', '[0-9]+');

        // 1.把setting注册进config
        if (!App::runningInConsole() && count(Schema::getColumnListing('settings'))) {
            // get all settings from the database
            $settings = SiteSetting::all();

            // bind all settings to the Laravel config, so you can call them like
            // Config::get('settings.contact_email')
            foreach ($settings as $key => $setting) {
                Config::set('settings.'.$setting->key, $setting->value);
            }
        }

        $this->mergeConfigFrom(
            __DIR__.'/../config/const.php', 'const'
        );

        // 2.注册中间件别名
        $routeMiddleware = [
            'admin'         => \Modules\Base\Middleware\Admin::class,
            'admin.logined' => \Modules\Base\Middleware\AdminLogined::class,
            'uhome'         => \Modules\Base\Middleware\Uhome::class,
            'uhome.logined' => \Modules\Base\Middleware\UhomeLogined::class,
            'jwt'           => \Modules\Base\Middleware\JWT::class,
            'throttle.web'  => \Modules\Base\Middleware\ThrottleWeb::class,
            'operation.log' => \Modules\Base\Middleware\OperationLog::class,
        ];

        foreach ($routeMiddleware as $key => $value) {
            Route::aliasMiddleware($key, $value);
        }

        // 载入发布各种
        $this->loadRoutes();
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'base');
        $this->loadViewsFrom(__DIR__.'/Views', 'base');

        // publish config file
        $this->publishes([
            __DIR__.'/../config' => config_path()
        ], 'config');

        // publish migration file
        $this->publishes([
            __DIR__.'/../resources/migrations' => database_path('migrations')
        ], 'migrations');

        // Article::created(function ($article) {
        //     \DB::table('base_user_act_log')->insert([
        //         [
        //             'user_id'   => $article->user_id,
        //             'act_type'  => 'article',
        //             'act_id'    => $article->id,
        //             'created_at'    => $article->created_at,
        //             'updated_at'    => $article->updated_at,
        //         ],
        //     ]);
        // });

        // Article::deleted(function ($article) {
        //     \DB::table('base_user_act_log')->where('act_type', 'article')->where('act_id', $article->id)
        //        ->update(['deleted_at' => Carbon::now()]);
        // });

        // Moment::created(function ($moment) {
        //     \DB::table('base_user_act_log')->insert([
        //         [
        //             'user_id'   => $moment->user_id,
        //             'act_type'  => 'moment',
        //             'act_id'    => $moment->id,
        //             'created_at'    => $moment->created_at,
        //             'updated_at'    => $moment->updated_at,
        //         ],
        //     ]);
        // });

        // Moment::deleted(function ($moment) {
        //     \DB::table('base_user_act_log')->where('act_type', 'moment')->where('act_id', $moment->id)
        //        ->update(['deleted_at' => Carbon::now()]);
        // });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        // 1.注册别名
        AliasLoader::getInstance([
            'BaseAspect'            => \Modules\Base\Libraries\BaseAspect::class,
            'BaseAspectInterface'   => \Modules\Base\Libraries\BaseAspectInterface::class,
            'BaseController'        => \Modules\Base\Libraries\BaseController::class,
            'BaseLogic'             => \Modules\Base\Libraries\BaseLogic::class,
            'BaseModel'             => \Modules\Base\Libraries\BaseModel::class,
//            'BaseService'       => \Apk\Base\Libraries\BaseService::class,
        ])->register();

        $this->app->singleton('aliyuncs',function($app){
            return new Aliyuncs\AliyuncsValidate();
        });

        $this->app->singleton('aliyunrealname',function($app){
            return new Aliyuncs\AliyunRealname();
        });

        $this->app->singleton('baiduimage',function($app){
            return new Baidu\ImageSearch();
        });
    }

    public function loadRoutes()
    {
        Route::middleware(['web'])
            ->namespace('Modules\Base\Controllers')
            ->group(__DIR__.'/../routes/admin.php');

        Route::prefix('api')
            ->middleware(['api'])
            ->namespace('Modules\Base\Controllers')
            ->group(__DIR__.'/../routes/api.php');

        Route::middleware(['web'])
            ->namespace('Modules\Base\Controllers')
            ->group(__DIR__.'/../routes/common.php');

        Route::middleware(['web'])
            ->namespace('Modules\Base\Controllers')
            ->group(__DIR__.'/../routes/front.php');

        Route::middleware(['api'])
            ->namespace('Modules\Base\Controllers')
            ->group(__DIR__.'/../routes/uhome.php');
    }

    public static function path()
    {
        return __DIR__;
    }

    public static function info()
    {
        return [
            "name"=> "Base",
            "slug"=> "base",
            "version"=> "1.0",
            "description"=> "BaseModule: the base of all module.",
            "enabled"=> true,
        ];
    }
}
