<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms;

use App\Platforms\AndroidPlatform;
use App\Platforms\Platform;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Class AndroidPlatformTest
 *
 * @package Tests\Unit\Platforms
 */
class AndroidPlatformTest extends TestCase
{

    /**
     * @test
     */
    public function it_extends_platform(): void
    {
        $plat = new AndroidPlatform();
        $this->assertInstanceOf(Platform::class, $plat);
    }

    /**
     * @test
     */
    public function it_return_the_right_identifier(): void
    {
        $plat = new AndroidPlatform();

        $this->assertSame('Android', $plat->getId());
    }

    /**
     * @test
     */
    public function it_return_the_right_mime_type(): void
    {
        $plat = new AndroidPlatform();

        $this->assertSame([
            'application/vnd.android.package-archive',
        ], $plat->getMimeTypes());
    }

    /**
     * @test
     */
    public function it_return_the_download_url()
    {
        $application = $this->makeApplicationModel();

        $build = $this->makeBuildModel($application->id, 'Android');

        $plat = new AndroidPlatform();

        $url = $plat->getDownloadUrl($application, $build, Storage::fake('cloud'));

        $this->assertIsString($url);
    }
}
