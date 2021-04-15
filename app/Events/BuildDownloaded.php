<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Build;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BuildDownloaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var Build */
    public $build;

    /** @var string */
    public $userAgent;

    /**
     * Create a new event instance.
     *
     * @param Build $build
     * @param string $userAgent
     */
    public function __construct(Build $build, string $userAgent)
    {
        $this->build = $build;
        $this->userAgent = $userAgent;
    }
}
