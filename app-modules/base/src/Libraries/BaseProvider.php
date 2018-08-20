<?php

namespace Apk\Base\Libraries;

use Apk\Base\Services\SiteSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use App;
use Blade;
use Request;
use Config;

class BaseProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 绑定Facades
        App::bind('mystorage', function() {
            return new \Apk\Base\Libraries\File\Storage;
        });

        // directive 在默认命令之后执行（扩展）
        Blade::directive('php', function($expression) {
            // $expression = trim($expression, '()');
            if (starts_with($expression, '(')) {
                $expression = substr($expression, 1, -1);
            }

            return "<?php $expression; ?>";
        });

        // extend 在默认命令之前执行（覆盖）
        Blade::extend(function($view, $compiler)
        {
            $callback = function ($match) {
                if ($match[1] == 'foreach') {
                    $match[0] = call_user_func(function($expression) {
                        $var = '$i';
                        return "<?php {$var} = 0; foreach{$expression}: ?>";
                    }, array_get($match, 3));
                }

                if ($match[1] == 'endforeach') {
                    $match[0] = call_user_func(function($expression) {
                        $var = '$i';
                        return "<?php {$var}++; endforeach; ?>";
                    }, array_get($match, 3));
                }

                return isset($match[3]) ? $match[0] : $match[0].$match[2];
            };

            return preg_replace_callback('/\B@(\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', $callback, $view);
        });

        $serv_setting = new SiteSetting();
        view()->share('setting', $serv_setting->getSiteSetting());
    }

    public function register()
    {
        // 1.注册别名
        AliasLoader::getInstance([
            'Apk'               => \Apk\Base\Facades\Apk::class,
            'BaseController'    => \Apk\Base\Libraries\BaseController::class,
            'BaseLogic'         => \Apk\Base\Libraries\BaseLogic::class,
            'BaseModel'         => \Apk\Base\Libraries\BaseModel::class,
            'BaseService'       => \Apk\Base\Libraries\BaseService::class,
        ])->register();

        // 2.注册apk们的服务提供者
        $fs = new Filesystem();
        $directories = $fs->directories(base_path('app-pkg'));

        foreach ($directories as $apkPath) {//echo($apkPath);var_dump($fs->exists($apkPath . '/Providers/AppServiceProvider.php'));
            if ($fs->exists($apkPath . '/apk.json') && $fs->exists($apkPath . '/Providers/ApkServiceProvider.php')) {
                $apkName = basename($apkPath);
                App::register('Apk\\' . $apkName . '\Providers\ApkServiceProvider');
            }
        }

//        if (Request::segment(1) == 'admin') {
//            Config::set('auth.model', \Apk\Base\Models\SiteSetting::class);
//            Config::set('auth.table', 'admin_user');
//        }
    }
}
