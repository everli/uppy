<?php

declare(strict_types=1);


namespace Tests\Feature\Http\Controllers\Api\V2;


use App\Events\UpdateCheck;
use App\Platforms\AndroidPlatform;
use Carbon\Carbon;
use Tests\TestCase;

class ApplicationApiControllerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_the_update_if_available()
    {
        // Create the application
        $application = $this->makeApplicationModel();

        // Select the platform
        $platform = new AndroidPlatform();

        // Create the build
        Carbon::setTestNow(Carbon::today()->subWeek());
        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.0.0',
            'available_from' => Carbon::today()->subWeek()
        ]);

        Carbon::setTestNow(Carbon::today()->addDays(6));
        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.1.1',
            'available_from' => Carbon::today()->subDay()
        ]);

        Carbon::setTestNow(Carbon::today()->addDay());

        $this->expectsEvents(UpdateCheck::class);

        $response = $this->post(route('api.v2.updates.get', [
            'application' => $application->slug,
            'platform' => $platform->getId(),
        ]), [
            'version' => '1.0.0',
            'device_id' => 'aUniqueId'
        ]);

        $response->assertSuccessful();
        $this->assertSame('1.1.1', $response->json('data.version'));
    }

    /**
     * @test
     */
    public function it_gives_a_validation_error_if_parameters_not_specified()
    {
        $application = $this->makeApplicationModel();
        $platform = new AndroidPlatform();

        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.0.0',
            'available_from' => Carbon::today()->subWeek()
        ]);

        $response = $this->post(route('api.v2.updates.get', [
            'application' => $application->slug,
            'platform' => $platform->getId(),
        ]));

        $response->assertJsonMissingValidationErrors(['device_id', 'version']);
    }

    /**
     * @test
     */
    public function it_gives_a_validation_error_if_version_not_specified()
    {
        $application = $this->makeApplicationModel();
        $platform = new AndroidPlatform();

        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.0.0',
            'available_from' => Carbon::today()->subWeek()
        ]);

        $response = $this->post(route('api.v2.updates.get', [
            'application' => $application->slug,
            'platform' => $platform->getId(),
        ]), ['device_id' => null]);

        $response->assertJsonMissingValidationErrors(['version']);
    }

    /**
     * @test
     */
    public function it_returns_a_not_found_if_no_update_availables()
    {
        // Create the application
        $application = $this->makeApplicationModel();

        // Select the platform
        $platform = new AndroidPlatform();

        // Create the build
        Carbon::setTestNow(Carbon::today()->subWeek());
        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.0.0',
            'available_from' => Carbon::today()->subWeek()
        ]);

        Carbon::setTestNow(Carbon::today()->addDay());

        $this->expectsEvents(UpdateCheck::class);

        $response = $this->post(route('api.v2.updates.get', [
            'application' => $application->slug,
            'platform' => $platform->getId(),
        ]), [
            'version' => '1.0.0',
            'device_id' => 'aUniqueId'
        ]);

        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function it_allows_null_device_id()
    {
        // Create the application
        $application = $this->makeApplicationModel();

        // Select the platform
        $platform = new AndroidPlatform();

        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.0.0',
            'available_from' => Carbon::today()->subWeek()
        ]);

        $response = $this->post(route('api.v2.updates.get', [
            'application' => $application->slug,
            'platform' => $platform->getId(),
            'version' => '1.0.0',
            'device_id' => null
        ]));

        $response->assertNotFound();
    }

}
