<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\V1;


use App\Models\Application;
use App\Models\Build;
use App\Models\BuildEvent;
use App\Models\Device;
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

        $v102build = factory(Build::class)->states('Android')->create([
            'version' => '1.0.2',
            'application_id' => $application->id,
            'partial_rollout' => false,
            'rollout_percentage' => 20,
        ]);
        factory(Device::class, 5)->create([
            'build_id' => $v102build->id,
            'application_id' => $application->id,
        ]);

        $build = factory(Build::class)->states('Android')->create([
            'version' => '1.1.1',
            'application_id' => $application->id,
            'partial_rollout' => true,
            'rollout_percentage' => 20,
        ]);
        factory(BuildEvent::class, 3)->create([
            'build_id' => $build->id,
            'event' => 'download',
        ]);
        factory(Device::class, 2)->create([
            'build_id' => $build->id,
            'application_id' => $application->id,
        ]);

        $response = $this->get(route('api.v1.applications.builds', ['application' => $application->id]));

        $this->assertArrayHasKey('Android', $response->json('data'));
        $this->assertCount(4, $response->json('data.Android'));

        $this->assertSame(false, $response->json('data.Android.2.partial_rollout'));
        $this->assertSame(100, $response->json('data.Android.2.rollout_percentage'));

        $expectedBuild = [
            'installations' => 2,
            'partial_rollout' => true,
            'rollout_percentage' => 20,
            'installations_percent' => 28,
        ];
        $buildData = $response->json('data.Android.3');
        foreach ($expectedBuild as $key => $value) {
            $this->assertSame($value, $buildData[$key], $key);
        }
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
