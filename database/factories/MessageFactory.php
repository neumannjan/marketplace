<?php

use Faker\Generator as Faker;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(\App\Message::class, function (Faker $faker) {
    return [
        'content' => $faker->words($faker->numberBetween(1, 20), true),
        'read' => true
    ];
});
