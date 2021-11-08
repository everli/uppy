<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Application;
use App\Models\Build;
use App\Platforms\AndroidPlatform;
use App\Repositories\BuildRepository;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BuildRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('s3');
    }

    /**
     * @test
     */
    public function create_works_as_expected_with_minimum_data()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Select the platform
        $platform = new AndroidPlatform();

        // Fake the build file
        $buildFile = UploadedFile::fake()->create('a-new-build.apk');

        // Define the version
        $version = '1.0.0';

        $attributes = [
            'platform' => $platform->getId(),
            'version' => $version,
            'file' => $buildFile,
        ];

        $builds = app()->make(BuildRepository::class);
        $build = $builds->create($application, $platform, $attributes);

        $buildPath = $application->slug.'/'.$application->slug.'-'.$platform->getId().'-'.$version.'.apk';

        Storage::cloud()->assertExists($buildPath);

        $this->assertDatabaseHas('builds', [
            'platform' => $platform->getId(),
            'version' => $version,
            'file' => $buildPath,
            'dismissed' => false,
            'available_from' => $build->available_from,
        ]);
    }

    /**
     * @test
     */
    public function create_works_as_expected_with_additional_data()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Select the platform
        $platform = new AndroidPlatform();

        // Fake the build file
        $buildFile = UploadedFile::fake()->create('a-new-build.apk');

        // Define the version
        $version = '1.0.0';

        // Save the timestamp
        $now = now();

        $attributes = [
            'platform' => $platform->getId(),
            'version' => $version,
            'file' => $buildFile,
            'dismissed' => 'true',
            'available_from' => $now->copy()->addDay()->toDateTimeString(),
        ];

        $builds = app()->make(BuildRepository::class);
        $build = $builds->create($application, $platform, $attributes);

        $buildPath = $application->slug.'/'.$application->slug.'-'.$platform->getId().'-'.$version.'.apk';

        Storage::cloud()->assertExists($buildPath);

        $this->assertDatabaseHas('builds', [
            'platform' => $platform->getId(),
            'version' => $version,
            'file' => $buildPath,
            'dismissed' => true,
            'available_from' => $build->available_from,
        ]);
    }

    /**
     * @test
     */
    public function getUpdate_returns_null_if_there_are_no_builds()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Select the platform
        $platform = new AndroidPlatform();

        $builds = app()->make(BuildRepository::class);
        $update = $builds->getUpdate($application, $platform, '1.0.0');

        $this->assertNull($update);
    }

    /**
     * @test
     */
    public function getUpdate_returns_null_if_the_last_build_has_the_same_version()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Select the platform
        $platform = new AndroidPlatform();

        // Create the build
        $this->makeBuildModel($application->id, 'Android', [
            'version' => '1.0.0',
        ]);

        $builds = app()->make(BuildRepository::class);
        $update = $builds->getUpdate($application, $platform, '1.0.0');

        $this->assertNull($update);
    }

    /**
     * @test
     */
    public function getUpdate_returns_null_if_the_last_build_has_the_greater_version()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Select the platform
        $platform = new AndroidPlatform();

        // Create the build
        $this->makeBuildModel($application->id, 'Android', [
            'version' => '1.0.0',
        ]);

        $builds = app()->make(BuildRepository::class);
        $update = $builds->getUpdate($application, $platform, '1.0.1');

        $this->assertNull($update);
    }

    /**
     * @test
     */
    public function getUpdate_returns_null_if_the_last_build_is_not_available_yet()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Select the platform
        $platform = new AndroidPlatform();

        // Create the build
        factory(Build::class)->states(['Android', 'postponed'])->create([
            'version' => '1.0.1',
            'application_id' => $application->id,
        ]);

        $builds = app()->make(BuildRepository::class);
        $update = $builds->getUpdate($application, $platform, '1.0.0');

        $this->assertNull($update);
    }

    /**
     * @test
     */
    public function getUpdate_returns_the_available_builds()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Select the platform
        $platform = new AndroidPlatform();

        // Create the build
        factory(Build::class)->states(['Android'])->create([
            'version' => '1.0.1',
            'application_id' => $application->id,
        ]);

        Carbon::setTestNow(now()->addDay());

        $builds = app()->make(BuildRepository::class);
        $update = $builds->getUpdate($application, $platform, '1.0.0');

        $this->assertInstanceOf(Build::class, $update);
        $this->assertEquals('1.0.1', $update->version);
    }

    /**
     * @test
     */
    public function getUpdate_returns_build_with_forced_flag_if_there_is_a_forced_build_not_yet_installed()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Select the platform
        $platform = new AndroidPlatform();

        // Create the build
        factory(Build::class)->states(['Android'])->create([
            'version' => '7.5.2',
            'application_id' => $application->id,
            'dismissed' => false,
            'available_from' => Carbon::now()->subMonth(),
        ]);

        factory(Build::class)->states(['Android'])->create([
            'version' => '7.5.3',
            'application_id' => $application->id,
            'dismissed' => true,
            'available_from' => Carbon::now()->subDay(),
        ]);

        factory(Build::class)->states(['Android'])->create([
            'version' => '7.5.4',
            'application_id' => $application->id,
            'dismissed' => false,
            'available_from' => Carbon::now(),
        ]);

        Carbon::setTestNow(now()->addDay());

        $builds = app()->make(BuildRepository::class);
        $update = $builds->getUpdate($application, $platform, '7.5.2');

        $this->assertInstanceOf(Build::class, $update);
        $this->assertEquals('7.5.4', $update->version);
        $this->assertFalse($update->dismissed);
    }

    /**
     * @test
     */
    public function getUpdate_returns_build_without_forced_flag_if_there_is_not_a_forced_build_not_yet_installed()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Select the platform
        $platform = new AndroidPlatform();

        // Create the build
        factory(Build::class)->states(['Android'])->create([
            'version' => '7.5.3',
            'application_id' => $application->id,
            'dismissed' => false,
            'available_from' => Carbon::now()->subDay(),
        ]);

        factory(Build::class)->states(['Android'])->create([
            'version' => '7.5.4',
            'application_id' => $application->id,
            'dismissed' => false,
            'available_from' => Carbon::now(),
        ]);

        Carbon::setTestNow(now()->addDay());

        $builds = app()->make(BuildRepository::class);
        $update = $builds->getUpdate($application, $platform, '7.5.2');

        $this->assertInstanceOf(Build::class, $update);
        $this->assertEquals('7.5.4', $update->version);
        $this->assertEquals(false, $update->dismissed);
    }

    /**
     * @test
     */
    public function getLastBuild_returns_null_if_there_are_no_builds()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Select the platform
        $platform = new AndroidPlatform();

        $builds = app()->make(BuildRepository::class);
        $update = $builds->getLastBuild($application, $platform);

        $this->assertNull($update);
    }

    /**
     * @test
     */
    public function getLastBuild_returns_null_if_the_last_build_is_not_available_yet()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Select the platform
        $platform = new AndroidPlatform();

        // Create the build
        factory(Build::class)->states(['Android', 'postponed'])->create([
            'version' => '1.0.1',
            'application_id' => $application->id,
        ]);

        $builds = app()->make(BuildRepository::class);
        $update = $builds->getLastBuild($application, $platform);

        $this->assertNull($update);
    }

    /**
     * @test
     */
    public function getLastBuild_returns_the_last_build()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Select the platform
        $platform = new AndroidPlatform();

        // Create the build
        factory(Build::class)->states(['Android'])->create([
            'version' => '1.0.0',
            'application_id' => $application->id,
        ]);

        $builds = app()->make(BuildRepository::class);
        $update = $builds->getLastBuild($application, $platform);

        $this->assertInstanceOf(Build::class, $update);
        $this->assertEquals('1.0.0', $update->version);
    }

    /**
     * @test
     */
    public function it_return_the_builds_grouped_by_platform()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Create the build for android
        factory(Build::class)->states(['Android'])->create([
            'version' => '1.0.0',
            'application_id' => $application->id,
        ]);

        // Create the build for ios
        factory(Build::class)->states(['iOS'])->create([
            'version' => '2.0.0',
            'application_id' => $application->id,
        ]);

        $builds = app()->make(BuildRepository::class);
        $buildsByPlatform = $builds->getByPlatform($application);

        $this->assertArrayHasKey('Android', $buildsByPlatform);
        $this->assertArrayHasKey('iOS', $buildsByPlatform);

        $this->assertEquals('1.0.0', $buildsByPlatform['Android'][0]->version);
        $this->assertEquals('2.0.0', $buildsByPlatform['iOS'][0]->version);
    }

    /**
     * @test
     */
    public function it_return_an_empty_array_for_builds_grouped_by_platform()
    {
        // Create the application
        $application = factory(Application::class)->create();

        $builds = app()->make(BuildRepository::class);
        $buildsByPlatform = $builds->getByPlatform($application);

        $this->assertEmpty($buildsByPlatform);
    }

    /**
     * @test
     */
    public function it_deletes_an_uploaded_build()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Select the platform
        $platform = new AndroidPlatform();

        $buildFile = UploadedFile::fake()->create('a-new-build.apk');
        $buildPath = $buildFile->storeAs(
            $application->slug,
            $application->slug.'-'.$platform->getId().'-1.0.0.'.$buildFile->getClientOriginalExtension(),
            ['disk' => config('filesystems.cloud')]
        );

        // Create the build for android
        $build = factory(Build::class)->states(['Android'])->create([
            'version' => '1.0.0',
            'application_id' => $application->id,
            'file' => $buildPath
        ]);

        Storage::cloud()->assertExists($buildPath);
        $this->assertDatabaseHas('builds', ['id' => $build->id]);

        $builds = app()->make(BuildRepository::class);
        $builds->delete($build);

        Storage::cloud()->assertMissing($buildPath);
        $this->assertDatabaseMissing('builds', ['id' => $build->id]);
    }

    /**
     * @test
     */
    public function it_saves_the_changelogs_for_a_build()
    {
        // Create the application
        $application = factory(Application::class)->create();

        // Select the platform
        $platform = new AndroidPlatform();

        // Fake the build file
        $buildFile = UploadedFile::fake()->create('a-new-build.apk');

        // Define the version
        $version = '1.0.0';

        // Save the timestamp
        $now = now();

        $attributes = [
            'platform' => $platform->getId(),
            'version' => $version,
            'file' => $buildFile,
            'forced' => 'true',
            'available_from' => $now->copy()->addDay()->toDateTimeString(),
            'changelogs' => [
                'en' => 'Hi',
                'it' => 'Ciao'
            ],
        ];

        $builds = app()->make(BuildRepository::class);
        $build = $builds->create($application, $platform, $attributes);


        $this->assertDatabaseHas('changelogs', [
            'build_id' => $build->id,
            'locale' => 'en',
            'content' => 'Hi',
        ]);
        $this->assertDatabaseHas('changelogs', [
            'build_id' => $build->id,
            'locale' => 'it',
            'content' => 'Ciao',
        ]);
    }
}
