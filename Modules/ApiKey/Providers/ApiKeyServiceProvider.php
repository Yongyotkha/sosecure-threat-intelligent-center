<?php

namespace Modules\ApiKey\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class ApiKeyServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('apikey.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'apikey'
        );
    }

    public function registerViews()
    {
        $viewPath = resource_path('views/modules/apikey');
        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/apikey';
        }, \Config::get('view.paths')), [$sourcePath]), 'apikey');
    }

    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/apikey');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'apikey');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'apikey');
        }
    }

    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    public function provides()
    {
        return [];
    }
}
