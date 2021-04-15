<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Application;
use App\Repositories\ApplicationRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('s3');
    }

    /**
     * @test
     */
    public function create_works_as_expected()
    {
        $attributes = factory(Application::class)->raw([
            'icon' => UploadedFile::fake()->image('icon.png'),
        ]);

        $applications = app()->make(ApplicationRepository::class);
        $application = $applications->create($attributes);

        Storage::cloud()->assertExists($application->icon);

        $this->assertDatabaseHas('applications', array_merge($attributes, [
            'icon' => $application->icon
        ]));
    }
}
