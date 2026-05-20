<?php

declare(strict_types=1);


namespace Tests\Feature\Http\Controllers\Api\V2;


use App\Events\UpdateCheck;
use App\Models\Device;
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
    public function it_allows_null_device_id(): void
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

    /**
     * @test
     */
    public function it_returns_the_update_when_device_id_is_in_partial_rollout_range(): void
    {
        // Create the application
        $application = $this->makeApplicationModel();

        // Select the platform
        $platform = new AndroidPlatform();

        $build = $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.0.0',
            'available_from' => Carbon::today()->subWeek(),
            'partial_rollout' => true,
            'rollout_percentage' => 50,
        ]);

        $devices = factory(Device::class, 20)->create([
            'build_id' => $build->id,
            'application_id' => $application->id,
        ]);

        $updateAvailable = 0;
        foreach ($devices as $device) {
            $response = $this->post(route('api.v2.updates.get', [
                'application' => $application->slug,
                'platform' => $platform->getId(),
            ]), [
                'version' => '0.9.9',
                'device_id' => $device->device_id
            ]);

            if ($response->json('data') === null) {
                $updateAvailable++;
            }
        }

        $this->assertSame(10, $updateAvailable);
    }

    /**
     * @test
     */
    public function it_return_the_last_build_if_device_is_not_in_the_partial_rollout_range(): void
    {
        // Create the application
        $application = $this->makeApplicationModel();

        // Select the platform
        $platform = new AndroidPlatform();

        $build0 = $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '0.9.9',
            'available_from' => Carbon::today()->subMonth(),
        ]);

        $build1 = $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.0.0',
            'available_from' => Carbon::today()->subWeek(),
        ]);

        $build2 = $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '2.0.0',
            'available_from' => Carbon::today()->subDay(),
            'partial_rollout' => true,
            'rollout_percentage' => 0, // this update is not available to anyone
        ]);

        $device = factory(Device::class)->create([
            'build_id' => $build0->id,
            'application_id' => $application->id,
        ]);

        $response = $this->post(route('api.v2.updates.get', [
            'application' => $application->slug,
            'platform' => $platform->getId(),
        ]), [
            'version' => $build0->version,
            'device_id' => $device->device_id
        ]);

        $this->assertSame($build1->version, $response->json('data.version'));
        $this->assertNotSame($build2->version, $response->json('data.version'));
    }

    /**
     * @test
     */
    public function it_return_no_available_update_if_not_in_partial_rollout_range(): void
    {
        // Create the application
        $application = $this->makeApplicationModel();

        // Select the platform
        $platform = new AndroidPlatform();

        $build0 = $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.0.0',
            'available_from' => Carbon::today()->subMonth(),
        ]);

        $build1 = $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '2.0.0',
            'available_from' => Carbon::today()->subDay(),
            'partial_rollout' => true,
            'rollout_percentage' => 0, // this update is not available to anyone
        ]);

        $device = factory(Device::class)->create([
            'build_id' => $build0->id,
            'application_id' => $application->id,
        ]);

        $response = $this->post(route('api.v2.updates.get', [
            'application' => $application->slug,
            'platform' => $platform->getId(),
        ]), [
            'version' => $build0->version,
            'device_id' => $device->device_id
        ]);

        $response->assertJsonFragment(['message' => 'No available update.']);
    }

    /**
     * @test
     */
    public function it_returns_forced_update_when_device_version_is_higher_and_not_in_catalog()
    {
        $application = $this->makeApplicationModel();
        $platform = new AndroidPlatform();

        // Production cluster only has an older build
        Carbon::setTestNow(Carbon::today()->subWeek());
        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '12.0.17',
            'available_from' => Carbon::today()->subWeek(),
        ]);

        Carbon::setTestNow(Carbon::today()->addDay());

        $this->expectsEvents(UpdateCheck::class);

        // Device was moved from beta to production: its version is not in
        // this cluster's catalog
        $response = $this->post(route('api.v2.updates.get', [
            'application' => $application->slug,
            'platform' => $platform->getId(),
        ]), [
            'version' => '12.0.35',
            'device_id' => 'aUniqueId'
        ]);

        $response->assertSuccessful();
        $this->assertSame('12.0.17', $response->json('data.version'));
        $this->assertTrue($response->json('data.forced'));
    }

    /**
     * @test
     */
    public function it_returns_forced_update_when_device_version_uses_unknown_suffix()
    {
        $application = $this->makeApplicationModel();
        $platform = new AndroidPlatform();

        Carbon::setTestNow(Carbon::today()->subWeek());
        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '12.0.16-beta',
            'available_from' => Carbon::today()->subWeek(),
        ]);

        Carbon::setTestNow(Carbon::today()->addDay());

        $this->expectsEvents(UpdateCheck::class);

        // Device on a stable version not present in this beta-only catalog
        $response = $this->post(route('api.v2.updates.get', [
            'application' => $application->slug,
            'platform' => $platform->getId(),
        ]), [
            'version' => '12.0.15',
            'device_id' => 'aUniqueId'
        ]);

        $response->assertSuccessful();
        $this->assertSame('12.0.16-beta', $response->json('data.version'));
        $this->assertTrue($response->json('data.forced'));
    }

    /**
     * @test
     */
    public function it_returns_non_forced_update_when_device_version_is_in_catalog()
    {
        $application = $this->makeApplicationModel();
        $platform = new AndroidPlatform();

        Carbon::setTestNow(Carbon::today()->subWeek());
        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.0.0',
            'available_from' => Carbon::today()->subWeek(),
        ]);

        Carbon::setTestNow(Carbon::today()->addDays(6));
        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.1.0',
            'available_from' => Carbon::today()->subDay(),
        ]);

        Carbon::setTestNow(Carbon::today()->addDay());

        $this->expectsEvents(UpdateCheck::class);

        // Device's version exists in the catalog → regular (non-forced) update
        $response = $this->post(route('api.v2.updates.get', [
            'application' => $application->slug,
            'platform' => $platform->getId(),
        ]), [
            'version' => '1.0.0',
            'device_id' => 'aUniqueId'
        ]);

        $response->assertSuccessful();
        $this->assertSame('1.1.0', $response->json('data.version'));
        $this->assertFalse($response->json('data.forced'));
    }

    /**
     * @test
     */
    public function it_returns_forced_update_when_device_version_in_catalog_is_dismissed_and_is_higher()
    {
        $application = $this->makeApplicationModel();
        $platform = new AndroidPlatform();

        Carbon::setTestNow(Carbon::today()->subWeek());

        // Latest healthy build available
        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '12.0.15',
            'available_from' => Carbon::today()->subWeek(),
        ]);

        // The device's version is in the catalog but has been dismissed,
        // so it must be treated as unavailable and the device pushed back
        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '12.0.16',
            'available_from' => Carbon::today()->subWeek(),
            'dismissed' => true,
        ]);

        Carbon::setTestNow(Carbon::today()->addDay());

        $this->expectsEvents(UpdateCheck::class);

        $response = $this->post(route('api.v2.updates.get', [
            'application' => $application->slug,
            'platform' => $platform->getId(),
        ]), [
            'version' => '12.0.16',
            'device_id' => 'aUniqueId'
        ]);

        $response->assertSuccessful();
        $this->assertSame('12.0.15', $response->json('data.version'));
        $this->assertTrue($response->json('data.forced'));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function forced_is_true_if_the_current_build_is_dismissed()
    {
        // Create the application
        $application = $this->makeApplicationModel();

        // Select the platform
        $platform = new AndroidPlatform();

        // Create the build
        Carbon::setTestNow(Carbon::today()->subWeek());
        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.0.0',
            'available_from' => Carbon::today()->subWeek(),
            'dismissed' => true
        ]);

        Carbon::setTestNow(Carbon::today()->addDays(6));
        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.1.1',
            'available_from' => Carbon::today()->subDay(),
            'dismissed' => false
        ]);

        Carbon::setTestNow(Carbon::today()->addDays(6));
        $this->makeBuildModel($application->id, $platform->getId(), [
            'version' => '1.1.1',
            'available_from' => Carbon::today()->subDay(),
            'dismissed' => false
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
        $this->assertTrue($response->json('data.forced'));
    }

}
