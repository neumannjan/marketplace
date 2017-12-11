<?php

use Faker\Generator as Faker;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(\App\Offer::class, function (Faker $faker) {
    return [
        'name' => $faker->catchPhrase,
        'description' => $faker->realText(400),
        'status' => $faker->biasedNumberBetween(0, 2, function ($i) {
            return sin($i * M_PI);
        })
    ];
});

$factory->state(\App\Offer::class, 'available', [
    'status' => \App\Offer::STATUS_AVAILABLE
]);

$factory->state(\App\Offer::class, 'inactive', [
    'status' => \App\Offer::STATUS_INACTIVE
]);
