<?php

namespace Modules\Oms;

use Illuminate\Support\ServiceProvider;
use Route;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        // $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadRoutes();
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'oms');
        $this->loadViewsFrom(__DIR__.'/Views', 'oms');

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
            ->namespace('Modules\Oms\Controllers')
            ->group(__DIR__.'/../routes/admin.php');

        Route::prefix('api')
            ->middleware(['api'])
            ->namespace('Modules\Oms\Controllers')
            ->group(__DIR__.'/../routes/api.php');
    }
}
