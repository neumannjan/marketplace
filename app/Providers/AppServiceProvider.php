<?php

namespace App\Providers;

use App\Helpers\DevPackages;
use App\Helpers\Money;
use Illuminate\Support\ServiceProvider;

/**
 * Laravel's main service provider
 *
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // money parser and formatter
        $this->app->singleton('money', function ($app) {
            /** @var \App $app */

            return new Money($app->getLocale());
        });

        $this->app->alias('money', Money::class);

        // dev composer packages helper
        $this->app->singleton('dev-packages', function () {
            return new DevPackages();
        });
        $this->app->alias('dev-packages', DevPackages::class);
    }
}
