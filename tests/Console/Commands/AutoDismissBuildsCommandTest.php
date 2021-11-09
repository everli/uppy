<?php

namespace Tests\Console\Commands;


use App\Console\Commands\AutoDismissBuildsCommand;
use App\Platforms\AndroidPlatform;
use Carbon\Carbon;
use Tests\TestCase;

class AutoDismissBuildsCommandTest extends TestCase
{

    /**
     * @test
     */
    public function it_dismiss_builds_after_x_days()
    {
        Carbon::setTestNow(Carbon::today());

        // Create the application
        $application = $this->makeApplicationModel();

        // Select the platform
        $platform = new AndroidPlatform();

        // Create the builds
        $build1 = $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.0.0',
            'available_from' => Carbon::today()->subDays(9),
            'dismissed' => false
        ]);

        $build2 = $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '2.0.0',
            'available_from' => Carbon::today()->subDays(8),
            'dismissed' => false
        ]);

        $build3 = $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '3.0.0',
            'available_from' => Carbon::today()->subDays(7),
            'dismissed' => false
        ]);

        $build4 = $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '4.0.0',
            'available_from' => Carbon::today()->subDays(5),
            'dismissed' => false
        ]);

        config()->set('build.dismiss_after_days', 7);

        $this->artisan(AutoDismissBuildsCommand::class)->assertExitCode(0);

        $this->assertTrue($build1->refresh()->dismissed);
        $this->assertTrue($build2->refresh()->dismissed);
        $this->assertFalse($build3->refresh()->dismissed);
        $this->assertFalse($build4->refresh()->dismissed);
    }

    /**
     * @test
     */
    public function it_gives_error_if_config_not_set()
    {
        config()->set('build.dismiss_after_days', -1);

        $this->artisan(AutoDismissBuildsCommand::class)->assertExitCode(1);
    }
}
