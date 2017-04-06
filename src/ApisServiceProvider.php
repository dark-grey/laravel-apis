<?php

namespace Sitesoft\LaravelApis;

use Illuminate\Support\ServiceProvider;

class ApisServiceProvider extends ServiceProvider {

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\ApisCreate::class,
                Console\ApisAddVersion::class,
                Console\SwaggerGen::class
            ]);
        }
        
        if (method_exists($this->app['router'], 'aliasMiddleware')) {
            $this->app['router']->aliasMiddleware('cors', \Barryvdh\Cors\HandleCors::class);
        } else {
            $this->app['router']->middleware('cors', \Barryvdh\Cors\HandleCors::class);
        }

        $this->app->registerDeferredProvider('October\Rain\Config\ConfigServiceProvider');
        $this->app->registerDeferredProvider('Barryvdh\Cors\ServiceProvider');

        $this->publishes([
            __DIR__ . '/../config/' => config_path()
        ]);
    }

    public function register()
    {

    }

}
