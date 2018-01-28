<?php

namespace App\Providers;

use App\Helpers\Money;
use Illuminate\Support\ServiceProvider;

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
    }
}
