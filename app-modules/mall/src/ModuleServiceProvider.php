<?php

namespace Modules\Mall;

use Illuminate\Support\ServiceProvider;
use Route;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->loadRoutes();
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'mall');
        $this->loadViewsFrom(__DIR__.'/Views', 'mall');

        // publish config file
        $this->publishes([
            __DIR__.'/../config' => config_path()
        ], 'config');

        // publish migration file
        $this->publishes([
            __DIR__.'/../resources/migrations' => database_path('migrations')
        ], 'migrations');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function loadRoutes()
    {
        Route::middleware(['web'])
            ->namespace('Modules\Mall\Controllers')
            ->group(__DIR__.'/../routes/admin.php');

        Route::middleware(['web'])
            ->namespace('Modules\Mall\Controllers')
            ->group(__DIR__.'/../routes/store.php');

        Route::prefix('api')
            ->middleware(['api'])
            ->namespace('Modules\Mall\Controllers')
            ->group(__DIR__.'/../routes/api.php');

        Route::middleware(['web'])
            ->namespace('Modules\Mall\Controllers')
            ->group(__DIR__.'/../routes/app.php');
    }

    public function info()
    {
        return [
            "name"=> "Mall",
            "slug"=> "mall",
            "version"=> "1.0",
            "description"=> "Module: the mall module.",
            "enabled"=> true,
        ];
    }
}
