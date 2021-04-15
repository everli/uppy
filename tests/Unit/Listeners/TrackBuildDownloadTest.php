<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners;


use App\Events\BuildDownloaded;
use App\Platforms\AndroidPlatform;
use Carbon\Carbon;
use Tests\TestCase;

class TrackBuildDownloadTest extends TestCase
{

    /**
     * @test
     */
    public function it_tracks_the_download_event()
    {
        $application = $this->makeApplicationModel();
        $platform = new AndroidPlatform();
        $build = $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.0.0',
            'available_from' => Carbon::today()->subWeek()
        ]);

        event(new BuildDownloaded($build, 'useragent'));

        $this->assertDatabaseHas('build_events', [
            'build_id' => $build->id,
            'event' => 'download',
            'user_agent' => 'useragent'
        ]);
    }

}
