<?php

namespace App\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Modules\Settings\Entities\Options;
use App\Services\SSHTunnelService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url)
    {
        \Schema::defaultStringLength(191);

        if (!Cache::has(settingsCacheName())) {
            if (file_exists(storage_path('installed'))) {
                Cache::remember(
                    settingsCacheName(),
                    now()->addDays(30),
                    function () {
                        $conf = [];
                        foreach (Options::select('value', 'config_key')->get()->toArray() as $setting) {
                            $conf[$setting['config_key']] = $setting['value'];
                        }
                        return $conf;
                    }
                );
            }
        }
        date_default_timezone_set(get_option('timezone', 'UTC'));

        if (env('REDIRECT_HTTPS', false)) {
            $url->forceScheme('https');
        }
        if (app()->environment('production')) {
            \DB::disableQueryLog();
        }
        // if (env('SSH_TUNNEL_AUTO', true)) {
        //     try {
        //         $tunnel = app(SSHTunnelService::class);

        //         if (!$tunnel->isTunnelRunning()) {
        //             $tunnel->createTunnel();
        //             sleep(2); 
        
        //         } else {
           
        //         }
        //     } catch (\Exception $e) {
                
        //     }
        // }
        if (env('SSH_TUNNEL_AUTO', true)) {
            if (!app()->runningUnitTests()) {
                $interval = (int) env('SSH_TUNNEL_CHECK_INTERVAL', 15);
                $cacheKey = 'ssh:tunnel:last-check';

                if (!Cache::has($cacheKey)) {
                    Cache::put($cacheKey, 1, $interval);

                    $lockKey = 'ssh:tunnel:create';
                    $lockTtl = max(5, (int) env('SSH_TUNNEL_LOCK_SEC', 20));

                    // ใช้ Cache::add() เป็น soft lock
                    if (Cache::add($lockKey, 1, $lockTtl)) {
                        try {
                            $tunnel = app(\App\Services\SSHTunnelService::class);
                            if (!$tunnel->isTunnelRunning()) {
                                $tunnel->createTunnel();
                            }
                        } catch (\Throwable $e) {
                            \Log::error('SSH TUNNEL error: ' . $e->getMessage());
                        } finally {
                            Cache::forget($lockKey);
                        }
                    } else {
                        \Log::warning('SSH TUNNEL: another create is in progress; skip');
                    }
                }
            }
        }
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        // if ($this->app->isLocal()) {
        //     $this->app->register(TelescopeServiceProvider::class);
        // }
    }
}
