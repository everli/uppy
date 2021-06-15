<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UpdateCheck;
use App\Models\Device;

class TrackApplicationVersion
{
    /**
     * Handle the event.
     *
     * @param  UpdateCheck  $event
     * @return void
     */
    public function handle(UpdateCheck $event)
    {
        if ($event->currentBuild === null || $event->deviceId === null) {
            return;
        }

        Device::query()
            ->updateOrCreate([
                'application_id' => $event->application->id,
                'device_id' => $event->deviceId,
            ], [
                'build_id' => $event->currentBuild->id
            ])->touch();
    }
}
