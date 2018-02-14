<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */

$factory->define(App\User::class, function (Faker $faker) {
    static $password;

    return [
        'username' => str_replace('.', '_', $faker->unique()->userName),
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret999'),
        'description' => $faker->boolean ? $faker->realText($faker->numberBetween(100, 200)) : '',
        'display_name' => $faker->boolean ? $faker->name : '',
        'remember_token' => str_random(10),
        'status' => $faker->biasedNumberBetween(0, 2, function ($i) {
            return sin($i * M_PI);
        })
    ];
});

$factory->state(\App\User::class, 'active', [
    'status' => \App\User::STATUS_ACTIVE
]);

$factory->state(\App\User::class, 'banned', [
    'status' => \App\User::STATUS_BANNED
]);

$factory->state(\App\User::class, 'inactive', [
    'status' => \App\User::STATUS_INACTIVE
]);
