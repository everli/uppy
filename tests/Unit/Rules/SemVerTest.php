<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\SemVer;
use Tests\TestCase;

/**
 * @package Tests\Unit\Rules
 */
class SemVerTest extends TestCase
{
    /**
     * @return array[]
     */
    public function dataProvider(): array
    {
        return [
            ['invalid_version' => 'foo', false],
            ['invalid_version' => 'f.o.o', false],
            ['invalid_version' => null, false],
            ['valid_version' => '1', true],
            ['valid_version' => '1.1', true],
            ['valid_version' => '1.1.1', true],
            ['valid_version' => '1.1.1-a', true],
            ['valid_version' => '1.1.1-rc', true],
        ];
    }

    /**
     * @test
     * @dataProvider dataProvider
     *
     * @param $version
     * @param $expected
     *
     * @return void
     */
    public function validation_works_as_expected($version, $expected): void
    {
        $rule = new SemVer();
        $this->assertEquals($expected, $rule->passes('version', $version));
    }
}
