<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\Application;
use App\Models\Build;
use App\Platforms\AndroidPlatform;
use App\Platforms\IOSPlatform;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

$factory->define(Build::class, function (Faker $faker) {
    return [
        'application_id' => factory(Application::class),
        'platform' => resolve($faker->randomElement(app('platform')->getSupportedPlatforms()))->getId(),
        'version' => '0.0.1',
        'file' => $faker->slug . '/' . Str::random() . '.' . $faker->fileExtension,
        'forced' => false,
        'available_from' => Carbon::now(),
    ];
});

$factory->state(Build::class, 'Android', function (Faker $faker) {
    if (!in_array(AndroidPlatform::class, app('platform')->getSupportedPlatforms())) {
        throw new RuntimeException('The Android platform is not supported.');
    }

    return [
        'platform' => (new AndroidPlatform())->getId(),
    ];
});

$factory->state(Build::class, 'iOS', function (Faker $faker) {
    if (!in_array(AndroidPlatform::class, app('platform')->getSupportedPlatforms())) {
        throw new RuntimeException('The iOS platform is not supported.');
    }

    return [
        'platform' => (new IOSPlatform())->getId(),
    ];
});


$factory->state(Build::class, 'postponed', function (Faker $faker) {
    return [
        'available_from' => Carbon::now()->addDay(),
    ];
});

$factory->state(Build::class, 'forced', function (Faker $faker) {
    return [
        'forced' => true,
    ];
});
