<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\BuildDownloaded;
use App\Events\UpdateCheck;
use App\Listeners\TrackApplicationVersion;
use App\Listeners\TrackBuildDownload;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        BuildDownloaded::class => [
            TrackBuildDownload::class,
        ],
        UpdateCheck::class => [
            TrackApplicationVersion::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
