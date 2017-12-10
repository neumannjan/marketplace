<?php

use Faker\Generator as Faker;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(\App\Image::class, function (Faker $faker) {
    return [
        'size_original' => '',
        'size_tiny' => '',
    ];
});
