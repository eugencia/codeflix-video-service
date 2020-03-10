<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Enums\Classification;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Faker\Generator as Faker;

$factory->define(Video::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence(4),
        'description' => $faker->text(100),
        'classification' => rand(1,4),
        'duration' => $faker->randomNumber(2),
        'release_at' => $faker->date,
    ];
});
