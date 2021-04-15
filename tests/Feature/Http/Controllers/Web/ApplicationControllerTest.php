<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Web;

use App\Models\Build;
use App\Platforms\PlatformService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationControllerTest extends TestCase
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

        $this->withHeader('Authorization', 'Bearer ' . config('auth.credentials.api_key'));
    }

    /**
     * @test
     */
    public function it_shows_the_homepage()
    {
        $response = $this->get('/');
        $response->assertSee(config('app.name'));
    }

    /**
     * @test
     */
    public function it_returns_404_if_the_icon_not_found()
    {
        $app = $this->createApplicationWithBuildsAndEvents();

        /** @var Build $build */
        $build = $app->builds()->first();

        $response = $this->get(route('applications.icon', [
            'application' => $app->slug,
            'platform' => $build->platform,
        ]));

        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function it_return_the_plist_xml()
    {
        $app = $this->createApplicationWithBuildsAndEvents();
        /** @var Build $build */
        $build = $app->builds()->first();

        $response = $this->get(route('applications.plist', [
            'application' => $app->slug,
            'platform' => $build->platform,
        ]));

        $platform = app()->make(PlatformService::class)->get($build->platform);

        $response->assertSuccessful();
        $response->assertSee($app->name);
        $response->assertSee($platform->getFileUrl($build, Storage::cloud()));
        $response->assertSee($build->version);
    }

    /**
     * @test
     */
    public function it_return_the_plist_xml_only_for_ios()
    {
        $app = $this->createApplicationWithBuildsAndEvents();
        /** @var Build $build */
        $build = $app->builds()->where('platform', 'Android')->first();

        $response = $this->get(route('applications.plist', [
            'application' => $app->slug,
            'platform' => $build->platform,
        ]));

        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function it_returns_the_app_icon()
    {
        $app = $this->createApplicationWithBuildsAndEvents();

        /** @var Build $build */
        $build = $app->builds()->first();

        $image = UploadedFile::fake()->image('icon.png');

        Storage::cloud()->put($app->icon, $image->get());

        $response = $this->get(route('applications.icon', [
            'application' => $app->slug,
            'platform' => $build->platform,
        ]));

        $response->assertSuccessful();
    }

    /** @test */
    public function it_redirect_to_the_correct_android_download_page()
    {
        $androidAgent = 'Mozilla/5.0 (Linux; Android 9; SM-G960F Build/PPR1.180610.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/74.0.3729.157 Mobile Safari/537.36';

        $app = $this->createApplicationWithBuildsAndEvents();

        $this->get(route('applications.redirect', [
            'application' => $app->slug,
        ]), ['user-agent' => $androidAgent])
            ->assertRedirect(url('/applications/' . $app->slug . '/Android'));
    }

    /** @test */
    public function it_redirect_to_the_correct_ios_download_page()
    {
        $iosAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148';

        $app = $this->createApplicationWithBuildsAndEvents();

        $this->get(route('applications.redirect', [
            'application' => $app->slug,
        ]), ['user-agent' => $iosAgent])
            ->assertRedirect(url('/applications/' . $app->slug . '/iOS'));
    }
}
