<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BuildDownloaded;

class TrackBuildDownload
{
    /**
     * Handle the event.
     *
     * @param BuildDownloaded $event
     *
     * @return void
     */
    public function handle(BuildDownloaded $event)
    {
        $build = $event->build;
        $build->events()->create(['event' => 'download', 'user_agent' => $event->userAgent]);
    }
}
