<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Serrvice provider for development tools.
 *
 * @package App\Providers
 */
class DevServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (env('APP_DEBUG') && \DevPackages::available()) {

            // ide helper (development)
            $this->app->register('Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider');
            $this->app->register('Barryvdh\Debugbar\ServiceProvider');
        }
    }
}
