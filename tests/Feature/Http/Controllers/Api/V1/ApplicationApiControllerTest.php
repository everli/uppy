<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\V1;


use App\Models\Application;
use App\Models\User;
use App\Platforms\AndroidPlatform;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApplicationApiControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('s3');
    }

    /**
     * @test
     */
    public function it_creates_a_new_application()
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        $attributes = factory(Application::class)->raw([
            'icon' => UploadedFile::fake()->image('icon.png'),
        ]);

        $response = $this->post(route('api.v1.applications.create'), $attributes);

        $response->assertSuccessful();
        $this->assertDatabaseHas('applications', $response->json('data'));
        Storage::cloud()->assertExists($response->json('data.icon'));
    }

    /**
     * @test
     *
     * @return void
     */
    public function events_requires_authentication(): void
    {
        $response = $this->getJson(route('api.v1.applications.events', ['application' => 'foo']));

        $response->assertUnauthorized();
    }

    /**
     * @test
     *
     * @return void
     */
    public function events_throw_a_404_if_the_application_slug_is_not_valid(): void
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        $response = $this->get(route('api.v1.applications.events', ['application' => 'foo']));

        $response
            ->assertNotFound()
            ->assertJson([
                "error" => [
                    "status" => 404,
                    "message" => "URI not found",
                ],
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function events_works_as_expected(): void
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        $application = $this->createApplicationWithBuildsAndEvents();

        $this->get(route('api.v1.applications.events', ['application' => $application->slug]))
            ->assertOk()
            ->assertJson([
                "data" => [
                    "Android" => [
                        [
                            "platform" => "Android",
                            "version" => "0.0.2",
                            "downloads" => 17,
                        ],
                        [
                            "platform" => "Android",
                            "version" => "0.0.1",
                            "downloads" => 4,
                        ],
                    ],
                    "iOS" => [
                        [
                            "platform" => "iOS",
                            "version" => "0.0.1",
                            "downloads" => 22,
                        ],
                    ],
                ],
            ]);
    }

    /**
     * @test
     */
    public function it_return_the_update_if_available()
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

        $response = $this->get(route('api.v1.updates.get', [
            'application' => $application->slug,
            'platform' => $platform->getId(),
            'version' => '1.0.0',
        ]));

        $response->assertSuccessful();
        $this->assertSame('1.1.1', $response->json('data.version'));
    }

    /**
     * @test
     */
    public function it_gives_no_updates_available_message()
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
        Carbon::setTestNow(Carbon::today()->addWeek());

        $response = $this->get(route('api.v1.updates.get', [
            'application' => $application->slug,
            'platform' => $platform->getId(),
            'version' => '1.0.0',
        ]));

        $response->assertNotFound();
        $this->assertSame('No available update.', $response->json('message'));
    }

    /** @test */
    public function it_return_application_index_paginated()
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        $application = $this->makeApplicationModel();

        $this->get(route('api.v1.applications.index'))
            ->assertOk()
            ->assertJsonFragment([
                'data' => [
                    [
                        'id' => $application->id,
                        'name' => $application->name,
                        'slug' => $application->slug,
                        'description' => $application->description,
                        'icon' => $application->icon,
                        'created_at' => $application->created_at->toDateTimeString(),
                        'updated_at' => $application->updated_at->toDateTimeString(),
                        'builds' => []
                    ]
                ],
            ]);
    }

    /** @test */
    public function it_return_a_single_application()
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        $application = $this->makeApplicationModel();

        $this->get(route('api.v1.applications.get', ['application' => $application->id]))
            ->assertOk()
            ->assertJsonFragment([
                'data' => [
                    'id' => $application->id,
                    'name' => $application->name,
                    'slug' => $application->slug,
                    'description' => $application->description,
                    'icon' => $application->icon,
                    'created_at' => $application->created_at->toDateTimeString(),
                    'updated_at' => $application->updated_at->toDateTimeString()
                ],
            ]);
    }
}
