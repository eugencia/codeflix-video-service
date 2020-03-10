<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Enums\CastMemberType;
use App\Models\CastMember;
use Faker\Generator as Faker;

$factory->define(CastMember::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'role' => rand(1, 3)
    ];
});
