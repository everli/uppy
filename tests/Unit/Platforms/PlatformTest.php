<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms;

use App\Models\Application;
use App\Models\Build;
use App\Platforms\Platform;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

/**
 * Class PlatformTest
 *
 * @package Tests\Unit\Platforms
 */
class PlatformTest extends TestCase
{
    /**
     * Setup test case
     */
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('filesystem.package'));
    }

    /**
     * @test
     */
    public function it_throws_exception_when_mimetype_is_null()
    {
        $this->expectException(RuntimeException::class);

        $instance = $this->provideEmptyInstance();

        $instance->getMimeTypes();
    }

    /**
     * @test
     */
    public function it_throws_exception_when_id_is_null()
    {
        $this->expectException(RuntimeException::class);

        $instance = $this->provideEmptyInstance();

        $instance->getId();
    }


    public function provideEmptyInstance()
    {
        return new class extends Platform {
            public function getDownloadUrl(Application $application, Build $build, \Illuminate\Contracts\Filesystem\Cloud $storage)
            {
                return null;
            }
        };
    }
}
