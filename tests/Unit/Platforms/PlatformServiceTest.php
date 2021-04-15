<?php


namespace Tests\Unit\Platforms;


use App\Exceptions\PlatformNotFoundException;
use App\Platforms\AndroidPlatform;
use App\Platforms\IOSPlatform;
use App\Platforms\PlatformService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PlatformServiceTest extends TestCase
{
    /**
     * @test
     */
    public function guessFromFile_guess_the_android_platform()
    {
        // Fake the build file
        $buildFile = UploadedFile::fake()->create('a-new-build.apk');

        $platform = app()->make(PlatformService::class)->guessFromFile($buildFile);

        $this->assertEquals(new AndroidPlatform(), $platform);
    }

    /**
     * @test
     */
    public function guessFromFile_guess_the_ios_platform()
    {
        // Fake the build file
        $buildFile = UploadedFile::fake()->create('a-new-build.ipa');

        $platform = app()->make(PlatformService::class)->guessFromFile($buildFile);

        $this->assertEquals(new IOSPlatform(), $platform);
    }

    /**
     * @test
     */
    public function guessFromFile_return_throws_exception_if_not_supported()
    {
        config(['uppy.platforms' => [AndroidPlatform::class]]);

        // Fake the build file
        $buildFile = UploadedFile::fake()->create('a-new-build.ipa');

        $this->expectException(PlatformNotFoundException::class);

        app()->make(PlatformService::class)->guessFromFile($buildFile);
    }

    /**
     * @test
     * @dataProvider providePlatformsId
     *
     * @param $id
     * @param $class
     */
    public function get_the_right_platform_by_id($id, $class)
    {
        $platform = app()->make(PlatformService::class)->get($id);

        $this->assertInstanceOf($class, $platform);
    }

    public function providePlatformsId()
    {
        return [
            'android' => [(new AndroidPlatform())->getId(), AndroidPlatform::class],
            'ios' => [(new IOSPlatform())->getId(), IOSPlatform::class],
        ];
    }

    /**
     * @test
     *
     */
    public function exception_when_platform_not_found()
    {
        $this->expectException(PlatformNotFoundException::class);

        app()->make(PlatformService::class)->get('Androios');
    }


    /**
     * @test
     *
     */
    public function exception_if_no_supported_platforms()
    {
        config()->set('uppy.platforms', []);

        $this->expectException(\RuntimeException::class);

        app()->make(PlatformService::class);
    }

    /**
     * @test
     *
     */
    public function it_return_supported_platforms()
    {
        $supportedPlatforms = [AndroidPlatform::class];

        config()->set('uppy.platforms', $supportedPlatforms);
        
        $actuals = app()->make(PlatformService::class)->getSupportedPlatforms();

        $this->assertSame($supportedPlatforms, $actuals);
    }
}
