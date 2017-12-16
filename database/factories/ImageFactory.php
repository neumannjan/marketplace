<?php

use Faker\Generator as Faker;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(\App\Image::class, function (Faker $faker) {
    /** @var Faker|\App\Tests\UnsplashImageFakerProvider $faker */
    $id = $faker->unsplashId();

    $width = 480;
    $height = mt_rand(230, 640);
    $original = $faker->unsplashUrl($width, $height, $id);
    $tiny = $faker->unsplashUrl($width / 20, floor($height / 20), $id);
    $icon = $faker->unsplashUrl(40, 40, $id);
    $icon_2x = $faker->unsplashUrl(80, 80, $id);

    return [
        'size_original' => $original,
        'size_tiny' => $tiny,
        'size_icon' => $icon,
        'size_icon_2x' => $icon_2x,
        'width' => $width,
        'height' => $height
    ];
});
