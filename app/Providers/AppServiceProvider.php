<?php

namespace App\Providers;

use App\Platforms\PlatformService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PlatformService::class, function () {
            return new PlatformService(config('uppy.platforms', []));
        });
        $this->app->alias(PlatformService::class, 'platform');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('app.enforce_https', false)) {
            URL::forceScheme('https');
        }
    }
}
