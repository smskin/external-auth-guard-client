<?php

namespace SMSkin\IdentityServiceClient\Guard\Session;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SMSkin\IdentityServiceClient\Guard\Session\Support\TokenStorage;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerStorage();
        $this->registerGuard();
    }

    private function registerStorage()
    {
        $this->app->singleton(TokenStorage::class, function ($app) {
            return new TokenStorage(
                $this->app['session.store'],
                $this->app['cookie'],
                $app['request'],
            );
        });
    }

    private function registerGuard()
    {
        $this->app['auth']->extend(config('identity-service-client.guards.session.driver.name'), function ($app, $name, array $config) {
            $guard = new Guard(
                config('identity-service-client.guards.session.name'),
                $app['auth']->createUserProvider($config['provider']),
                $this->app['session.store'],
                $this->app['cookie'],
                $app['request'],
                config('identity-service-client.debug', false)
            );

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });

        config(['auth.guards.identity-service-client-session' => [
            'driver' => config('identity-service-client.guards.session.driver.name'),
            'provider' => 'users',
        ]]);
    }
}