<?php

namespace App\Providers;

use App\Tests\UnsplashImageFakerProvider;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Illuminate\Support\ServiceProvider;

/**
 * Defines observers of model classes.
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(FakerGenerator::class, function ($app) {
            $faker = FakerFactory::create($app['config']->get('app.faker_locale', 'en_US'));
            $faker->addProvider(new UnsplashImageFakerProvider($faker));
            return $faker;
        });
    }
}
