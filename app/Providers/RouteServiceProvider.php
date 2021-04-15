<?php

namespace App\Providers;

use App\Exceptions\PlatformNotFoundException;
use App\Platforms\PlatformService;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers\Web';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/applications';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Route::bind('platform', function ($value, \Illuminate\Routing\Route $route) {
            $concrete = app()->make(PlatformService::class)->get($value);
            $binding = $route->bindingFieldFor('platform');

            if ($binding !== null && $binding !== $value) {
                throw new PlatformNotFoundException();
            }

            return $concrete;
        });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api/v1')
            ->middleware('api')
            ->name('api.v1.')
            ->namespace('App\Http\Controllers\Api\V1')
            ->group(base_path('routes/api/v1.php'));

        Route::prefix('api/v2')
            ->middleware('api')
            ->name('api.v2.')
            ->namespace('App\Http\Controllers\Api\V2')
            ->group(base_path('routes/api/v2.php'));
    }
}
