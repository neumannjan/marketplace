<?php

use Faker\Generator as Faker;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(\App\Offer::class, function (Faker $faker) {
    return [
        'name' => $faker->words(3, true),
        'description' => $faker->paragraphs(3, true),
        'status' => $faker->biasedNumberBetween(0, 2, function($i){return sin($i*2);})
    ];
});

$factory->state(\App\Offer::class, 'available', [
    'status' => \App\Offer::STATUS_AVAILABLE
]);

$factory->state(\App\Offer::class, 'inactive', [
    'status' => \App\Offer::STATUS_INACTIVE
]);
