<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\Application;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Application::class, function (Faker $faker) {
    $slug = $faker->slug(3);

    return [
        'name' => $faker->text(20),
        'slug' => $faker->slug(3),
        'description' => $slug,
        'icon' => $slug . '/icon.png',
    ];
});
