<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Application;
use App\Models\Build;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateCheck
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var string
     */
    public $deviceId;

    /**
     * @var Application
     */
    public $application;

    /**
     * @var Build
     */
    public $currentBuild;

    /**
     * Create a new event instance.
     *
     * @param string|null $deviceId
     * @param Application $application
     * @param Build|null $currentBuild
     */
    public function __construct(?string $deviceId, Application $application, ?Build $currentBuild)
    {
        $this->deviceId = $deviceId;
        $this->application = $application;
        $this->currentBuild = $currentBuild;
    }
}
