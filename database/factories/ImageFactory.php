<?php

use Faker\Generator as Faker;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(\App\Image::class, function (Faker $faker) {
    /** @var Faker|\App\Tests\UnsplashImageFakerProvider $faker */
    $id = $faker->unsplashId();

    $width = 480;
    $height = mt_rand(230, 640);
    $orig = $faker->unsplashUrl($width, $height, $id);
    $tiny = $faker->unsplashUrl($width / 20, floor($height / 20), $id);

    return [
        'size_original' => $orig,
        'size_tiny' => $tiny,
        'width' => $width,
        'height' => $height
    ];
});
