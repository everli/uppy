<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms;

use App\Platforms\IOSPlatform;
use App\Platforms\Platform;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Class IOSPlatformTest
 *
 * @package Tests\Unit\Platforms
 */
class IOSPlatformTest extends TestCase
{
    /**
     * @test
     */
    public function it_extends_platform(): void
    {
        $plat = new IOSPlatform();
        $this->assertInstanceOf(Platform::class, $plat);
    }

    /**
     * @test
     */
    public function it_return_the_right_identifier(): void
    {
        $plat = new IOSPlatform();

        $this->assertSame('iOS', $plat->getId());
    }

    /**
     * @test
     */
    public function it_return_the_right_mime_type(): void
    {
        $plat = new IOSPlatform();

        $this->assertSame([
            'application/octet-stream',
        ], $plat->getMimeTypes());
    }

    /**
     * @test
     */
    public function it_return_the_download_url()
    {
        $application = $this->makeApplicationModel();

        $build = $this->makeBuildModel($application->id, 'iOS');

        $plat = new IOSPlatform();

        $url = $plat->getDownloadUrl($application, $build, Storage::fake('cloud'));

        $this->assertSame("itms-services://?action=download-manifest&url=".urlencode(route('applications.plist', [$application->slug, $plat->getId(), $build->plist_url])), $url);
    }
}
