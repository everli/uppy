<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Models\Application;
use App\Models\Build;
use App\Platforms\AndroidPlatform;
use App\Rules\NewVersion;
use Tests\TestCase;

class NewVersionTest extends TestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * Initialize the application.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->application = factory(Application::class)->create();
    }

    /**
     * @test
     */
    public function validation_passes_if_it_is_the_first_build_for_the_given_platform()
    {
        // create a greater version of the same application but different platform
        factory(Build::class)->state('iOS')->create(['version' => '1.0.0', 'application_id' => $this->application->id]);

        $rule = new NewVersion($this->application, new AndroidPlatform());
        $this->assertEquals(true, $rule->passes('versions', '0.0.1'));
    }

    /**
     * @test
     */
    public function validation_passes_if_the_version_is_greater()
    {
        factory(Build::class)->state('Android')->create(['version' => '1.0.0', 'application_id' => $this->application->id]);

        $rule = new NewVersion($this->application, new AndroidPlatform());
        $this->assertEquals(true, $rule->passes('versions', '1.0.1'));
    }

    /**
     * @test
     */
    public function validation_doesnt_pass_if_version_is_lower()
    {
        factory(Build::class)->state('Android')->create(['version' => '1.0.1', 'application_id' => $this->application->id]);

        $rule = new NewVersion($this->application, new AndroidPlatform());
        $this->assertEquals(false, $rule->passes('versions', '1.0.1'));
        $this->assertEquals("The Android version of {$this->application->name} must be greater than 1.0.1", $rule->message());
    }
}
