<?php

namespace App\Providers;

use App\Observers\UserObserver;
use App\Tests\UnsplashImageFakerProvider;
use App\User;
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
        User::observe(UserObserver::class);
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
