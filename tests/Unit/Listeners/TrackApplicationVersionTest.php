<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners;


use App\Events\UpdateCheck;
use App\Platforms\AndroidPlatform;
use Carbon\Carbon;
use Tests\TestCase;

class TrackApplicationVersionTest extends TestCase
{

    /**
     * @test
     */
    public function it_tracks_the_device_build()
    {
        $application = $this->makeApplicationModel();
        $platform = new AndroidPlatform();
        $build = $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.0.0',
            'available_from' => Carbon::today()->subWeek()
        ]);

        event(new UpdateCheck('id', $application, $build));

        $this->assertDatabaseHas('devices', [
            'device_id' => 'id',
            'application_id' => $application->id,
            'build_id' => $build->id,
        ]);

        $build = $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.1.0',
            'available_from' => Carbon::today()->subDay()
        ]);

        event(new UpdateCheck('id', $application, $build));

        $this->assertDatabaseHas('devices', [
            'device_id' => 'id',
            'application_id' => $application->id,
            'build_id' => $build->id,
        ]);
    }

    /**
     * @test
     */
    public function doesnt_create_events_if_no_builds()
    {
        $application = $this->makeApplicationModel();

        event(new UpdateCheck('id', $application, null));

        $this->assertDatabaseMissing('devices', ['device_id' => 'id']);
    }

    /**
     * @test
     */
    public function doesnt_create_events_if_device_id_is_null()
    {
        $application = $this->makeApplicationModel();
        $platform = new AndroidPlatform();
        $build = $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.1.0',
             'available_from' => Carbon::today()->subDay()
        ]);

        event(new UpdateCheck(null, $application, $build));

        $this->assertDatabaseMissing('devices', ['device_id' => null]);
    }

}
