<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\Build;
use App\Models\BuildEvent;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(BuildEvent::class, function (Faker $faker) {
    return [
        'build_id' => factory(Build::class)->create(),
        'event' => 'download',
        'user_agent' => $faker->userAgent,
    ];
});
