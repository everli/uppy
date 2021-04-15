<?php

namespace Tests;

use App\Models\Application;
use App\Models\Build;
use App\Models\BuildEvent;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    /**
     * @param  array  $attributes
     *
     * @return Application
     */
    public function makeApplicationModel(array $attributes = []): Application
    {
        return factory(Application::class)->create($attributes);
    }

    /**
     * @param  int  $applicationId
     * @param  string  $state
     * @param  array  $attributes
     *
     * @return Build
     */
    public function makeBuildModel(int $applicationId, string $state, array $attributes = []): Build
    {
        return factory(Build::class)->state($state)->create(array_merge_recursive(['application_id' => $applicationId], $attributes));
    }

    /**
     * Create application with 3 builds (2 for Android and 1 for iOS)
     * and various events
     *
     * @return Application
     */
    protected function createApplicationWithBuildsAndEvents()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        /** @var Application $application */
        $application = factory(Application::class)->create();

        /** @var Build $iOS001 */
        $iOS001 = factory(Build::class)->states('iOS')->create(['application_id' => $application->id, 'created_at' => $now]);

        /** @var Build $android001 */
        $android001 = factory(Build::class)->state('Android')->create(['application_id' => $application->id, 'created_at' => $now]);

        /** @var Build $android002 */
        $android002 = factory(Build::class)->state('Android')->create(['application_id' => $application->id, 'version' => '0.0.2', 'created_at' => $now->addDay()]);

        factory(BuildEvent::class, 22)->create(['build_id' => $iOS001->id]);
        factory(BuildEvent::class, 4)->create(['build_id' => $android001->id]);
        factory(BuildEvent::class, 17)->create(['build_id' => $android002->id]);

        // we add an event "fake" and we not expect this in the response
        factory(BuildEvent::class, 2)->create(['build_id' => $iOS001->id, 'event' => 'fake']);

        return $application;
    }
}
