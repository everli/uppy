<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Device;

class DeviceRepository
{
    /**
     * @param int $applicationId
     * @return int
     */
    public function getApplicationActiveDevices(int $applicationId): int
    {
        $threshold = now()
            ->subDays(config('uppy.active_device_threshold'))
            ->toDateTimeString();

        return Device::query()
            ->where('updated_at', '>', $threshold)
            ->where('application_id', $applicationId)
            ->count();
    }
}
