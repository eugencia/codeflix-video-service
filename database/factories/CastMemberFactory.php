<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Enums\CastMemberType;
use App\Enums\Role;
use App\Models\CastMember;
use Faker\Generator as Faker;

$factory->define(CastMember::class, function (Faker $faker) {

    $arr = [CastMember::ACTOR, CastMember::DIRECTOR];

    return [
        'name' => $faker->unique()->name,
        'role' => $arr[array_rand($arr)]
    ];
});
