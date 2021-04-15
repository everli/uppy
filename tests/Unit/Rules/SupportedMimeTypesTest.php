<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\SupportedMimeTypes;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * @package Tests\Unit\Rules
 */
class SupportedMimeTypesTest extends TestCase
{
    /**
     * @return array[]
     */
    public function dataProvider(): array
    {
        return [
            ['package' => null, false],
            ['package' => UploadedFile::fake()->create('file.pdf'), 'expected_validation_result' => false],
            ['package' => UploadedFile::fake()->create('file.apk'), 'expected_validation_result' => true],
            ['package' => UploadedFile::fake()->create('file.ipa'), 'expected_validation_result' => true],
        ];
    }

    /**
     * @test
     * @dataProvider dataProvider
     *
     * @param $package
     * @param $expected
     *
     * @return void
     */
    public function validation_works_as_expected($package, $expected): void
    {
        $rule = new SupportedMimeTypes();
        $this->assertEquals($expected, $rule->passes('package', $package));
    }
}
