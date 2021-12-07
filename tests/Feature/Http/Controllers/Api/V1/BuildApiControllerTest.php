<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\V1;


use App\Models\Application;
use App\Models\Build;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BuildApiControllerTest extends TestCase
{
    /**
     * Initialize storage and add bearer token to header.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('s3');
    }

    /**
     * @test
     *
     * @return void
     */
    public function create_requires_authentication(): void
    {
        $response = $this->postJson(route('api.v1.builds.create', ['application' => 30]));

        $response->assertUnauthorized();
    }

    /**
     * @test
     *
     * @return void
     */
    public function create_throw_a_404_if_the_application_id_not_valid(): void
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        $response = $this->post(route('api.v1.builds.create', ['application' => 'foo']), []);

        $response->assertNotFound();
    }

    /**
     * @test
     *
     * @return void
     */
    public function create_throw_a_422_if_the_required_data_are_missing(): void
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        // Create the application
        $application = factory(Application::class)->create();

        $response = $this->post(route('api.v1.builds.create', ['application' => $application->id]), []);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                "error" => [
                    "status" => 422,
                    "message" => "The given data was invalid.",
                    "errors" => [
                        "version" => [
                            "The version field is required."
                        ],
                        "file" => [
                            "The file field is required."
                        ],
                    ]
                ]
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function create_throw_a_422_if_the_data_are_not_valid(): void
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        // Create the application
        $application = factory(Application::class)->create();

        $response = $this->post(route('api.v1.builds.create', ['application' => $application->id]), [
            "version" => "foo",
            "file" => UploadedFile::fake()->create('foo.txt'),
            "available_from" => "bar",
            "dismissed" => "maybe",
        ]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                "error" => [
                    "status" => 422,
                    "message" => "The given data was invalid.",
                    "errors" => [
                        "version" => [
                            "The version must be a valid version string."
                        ],
                        "file" => [
                            "The file must be a supported mime type."
                        ],
                        "available_from" => [
                            "The available from is not a valid date.",
                            "The available from must be a date after or equal to now."
                        ],
                        "dismissed" => [
                            "The field dismissed must be a boolean."
                        ],
                    ]
                ]
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function create_throw_a_422_if_the_version_is_not_new(): void
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        // Create the application
        $application = factory(Application::class)->create();

        // Create the build
        factory(Build::class)->states(['Android', 'postponed'])->create([
            'version' => '1.0.1',
            'application_id' => $application->id
        ]);

        // Get the response
        $response = $this->post(route('api.v1.builds.create', ['application' => $application->id]), [
            'version' => '1.0.0',
            'file' => UploadedFile::fake()->create('a-new-build.apk'),
        ]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                "error" => [
                    "status" => 422,
                    "message" => "The given data was invalid.",
                    "errors" => [
                        "version" => [
                            "The Android version of {$application->name} must be greater than 1.0.1"
                        ],
                    ]
                ]
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function create_creates_a_new_build(): void
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        // Create the application
        $application = factory(Application::class)->create();

        // Save the timestamp
        $now = now();
        Carbon::setTestNow($now);

        // Get the response
        $response = $this->post(route('api.v1.builds.create', ['application' => $application->id]), [
            'version' => '1.0.0',
            'file' => UploadedFile::fake()->create('a-new-build.apk'),
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                "data" => [
                    "platform" => "Android",
                    "version" => "1.0.0",
                    "file" => $application->slug.'/'.$application->slug.'-Android-1.0.0.apk',
                    "dismissed" => false,
                    "application_id" => $application->id,
                    "available_from" => $now->toDateTimeString(),
                ]
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function create_creates_a_new_build_with_changelogs(): void
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        // Create the application
        $application = factory(Application::class)->create();

        // Save the timestamp
        $now = now();
        Carbon::setTestNow($now);

        // Get the response
        $response = $this->post(route('api.v1.builds.create', ['application' => $application->id]), [
            'version' => '1.0.0',
            'file' => UploadedFile::fake()->create('a-new-build.apk'),
            'changelogs' => [
                'en' => 'Hi',
                'it' => 'Ciao',
            ]
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                "data" => [
                    "platform" => "Android",
                    "version" => "1.0.0",
                    "file" => $application->slug.'/'.$application->slug.'-Android-1.0.0.apk',
                    "dismissed" => false,
                    "application_id" => $application->id,
                    "available_from" => $now->toDateTimeString(),
                ]
            ]);

        $this->assertDatabaseHas('changelogs', [
            'locale' => 'en',
            'content' => 'Hi',
        ]);
        $this->assertDatabaseHas('changelogs', [
            'locale' => 'it',
            'content' => 'Ciao',
        ]);
    }

    /**
     * @test
     */
    public function it_returns_a_stored_build()
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        // Create the application
        $application = factory(Application::class)->create();

        // Create the build
        $build = factory(Build::class)->states('Android')->create([
            'version' => '1.0.1',
            'application_id' => $application->id,
            'dismissed' => false,
        ]);

        $response = $this->get(route('api.v1.builds.show', ['build' => $build->id]));

        $this->assertSame($build->id, $response->json('data.id'));
        $this->assertSame($build->version, $response->json('data.version'));
        $this->assertSame($build->forced, $response->json('data.forced'));
        $this->assertSame($build->available_from->toDateTimeString(), $response->json('data.available_from'));
    }

    /**
     * @test
     */
    public function it_update_a_build_to_forced()
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        // Create the application
        $application = factory(Application::class)->create();

        // Create the build
        $build = factory(Build::class)->states('Android')->create([
            'version' => '1.0.1',
            'application_id' => $application->id,
            'dismissed' => false,
        ]);

        $response = $this->post(route('api.v1.builds.update', ['build' => $build->id]), [
            'dismissed' => true
        ]);

        $this->assertTrue($response->json('data.dismissed'));
    }

    /**
     * @test
     */
    public function it_list_the_builds()
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        // Create the application
        $application = factory(Application::class)->create();

        // Create some builds
        factory(Build::class)->states('Android')->create([
            'version' => '1.0.0',
            'application_id' => $application->id
        ]);

        factory(Build::class)->states('Android')->create([
            'version' => '1.0.1',
            'application_id' => $application->id
        ]);

        factory(Build::class)->states('Android')->create([
            'version' => '1.1.1',
            'application_id' => $application->id
        ]);

        $response = $this->get(route('api.v1.applications.builds', ['application' => $application->id]));

        $this->assertArrayHasKey('Android', $response->json('data'));
        $this->assertCount(3, $response->json('data.Android'));
    }

    /**
     * @test
     */
    public function it_deletes_a_build()
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        $application = factory(Application::class)->create();

        // Create a build
        $build = factory(Build::class)->states('Android')->create([
            'version' => '1.0.0',
            'application_id' => $application->id
        ]);
        $this->assertDatabaseHas('builds', ['id' => $build->id]);

        $this->delete(route('api.v1.builds.delete', ['build' => $build->id]))
            ->assertNoContent();

        $this->assertDatabaseMissing('builds', ['id' => $build->id]);
    }

    /**
     * @test
     * @dataProvider providesForcedFlags
     */
    public function create_accepts_different_forced_flags($dismissed): void
    {
        Sanctum::actingAs(
            factory(User::class)->create(),
            ['*']
        );

        // Create the application
        $application = factory(Application::class)->create();

        // Get the response
        $response = $this->post(route('api.v1.builds.create', ['application' => $application->id]), [
            'version' => '1.0.0',
            'file' => UploadedFile::fake()->create('a-new-build.apk'),
            'dismissed' => $dismissed
        ]);

        $response->assertCreated();
        $this->assertSame(filter_var($dismissed, FILTER_VALIDATE_BOOLEAN), $response->json('data.dismissed'));
    }

    /**
     * @return \Generator
     */
    public function providesForcedFlags(): \Generator
    {
        yield [true];
        yield [false];
        yield ['true'];
        yield ['false'];
        yield ['on'];
        yield ['off'];
    }

}
