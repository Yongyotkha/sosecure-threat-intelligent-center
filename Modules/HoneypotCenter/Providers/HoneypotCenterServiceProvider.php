<?php

namespace Modules\HoneypotCenter\Providers;

use Illuminate\Support\ServiceProvider;

class HoneypotCenterServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig()
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('honeypot.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'honeypot');
    }

    protected function registerViews()
    {
        $viewPath = resource_path('views/modules/honeypotcenter');
        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/honeypotcenter';
        }, \Config::get('view.paths')), [$sourcePath]), 'honeypotcenter');
    }
}
