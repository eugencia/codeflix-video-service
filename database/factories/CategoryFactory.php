<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Category;
use Faker\Generator as Faker;

$factory->define(Category::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->sentence(3),
        'description' => rand(1, 10) % 2 == 0 ? $faker->sentence() : null,
        'is_active' => true
    ];
});
