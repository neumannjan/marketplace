<?php

namespace App\Providers;

use App\Auth\UserProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Service provider for registering custom authentication / authorization
 * classes
 *
 * @package App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies
        = [
            'App\Model' => 'App\Policies\ModelPolicy',
        ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Register custom user provider
        \Auth::provider('custom', function ($app, array $config) {
            /** @var Application $app */
            return new UserProvider($this->app['hash'], $config['model']);
        });
    }
}
