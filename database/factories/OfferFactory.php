<?php

use Faker\Generator as Faker;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(\App\Offer::class, function (Faker $faker) {
    return [
        'name' => $faker->words(3, true),
        'description' => $faker->boolean ? $faker->realText($faker->numberBetween(100, 400)) : '',
        'listed_at' => $faker->dateTimeBetween('-2 years'),
        'price_value' => $faker->randomFloat(3, 0, 10000),
        'currency_code' => $faker->currencyCode,
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
