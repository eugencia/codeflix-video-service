<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Enums\Classification;
use App\Models\Video;
use Faker\Generator as Faker;

$factory->define(Video::class, function (Faker $faker) {

    $arr = Video::CLASSIFICATION;

    return [
        'title' => $faker->sentence(4),
        'description' => $faker->text(100),
        'classification' => $arr[array_rand($arr)],
        'duration' => $faker->randomNumber(2),
        'release_at' => $faker->date,
    ];
});
