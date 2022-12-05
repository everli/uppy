<?php

use App\Models\Application;
use App\Models\Build;
use App\Models\Device;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Application::class, 20)
            ->create()
            ->each(function (Application $application) {
                factory(Build::class, 10)
                    ->create(['application_id' => $application->id])
                    ->each(function (Build $build) use ($application) {
                        factory(Device::class)
                            ->create([
                                'build_id' => $build->id,
                                'application_id' => $application->id,
                                ]);
                    });
            });
    }
}
