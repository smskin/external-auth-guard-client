<?php

namespace SMSkin\IdentityServiceClient;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SMSkin\IdentityServiceClient\Guard\JwtGuard;
use SMSkin\IdentityServiceClient\Guard\JWT;
use SMSkin\IdentityServiceClient\Guard\SessionGuard;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->loadConfig();

        if (app()->runningInConsole()) {
            $this->registerMigrations();
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(Api\Providers\ServiceProvider::class);
        $this->app->register(Guard\Providers\ServiceProvider::class);

        $this->registerConfig();
        $this->registerBaseAuthGuard();
    }

    private function loadConfig()
    {
        $configPath = __DIR__ . '/../config/identity-service-client.php';
        $this->publishes([
            $configPath => app()->configPath('identity-service-client.php'),
        ], 'identity-service-client');
    }

    private function registerConfig()
    {
        $configPath = __DIR__ . '/../config/identity-service-client.php';
        $this->mergeConfigFrom($configPath, 'identity-service-client');
    }

    private function registerBaseAuthGuard()
    {
        $this->app['auth']->extend(config('identity-service-client.guards.jwt.driver.name'), function ($app, $name, array $config) {
            $guard = new JwtGuard(
                app(JWT::class),
                $app['auth']->createUserProvider($config['provider']),
                $app['request'],
                $app['events'],
                config('identity-service-client.guards.jwt.name')
            );

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });

        $this->app['auth']->extend(config('identity-service-client.guards.session.driver.name'), function ($app, $name, array $config) {
            $guard = new SessionGuard(
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

        config(['auth.guards.identity-service-client-jwt' => [
            'driver' => config('identity-service-client.guards.jwt.driver.name'),
            'provider' => 'users',
        ]]);

        config(['auth.guards.identity-service-client-session' => [
            'driver' => config('identity-service-client.guards.session.driver.name'),
            'provider' => 'users',
        ]]);
    }

    private function registerMigrations()
    {
        $this->publishes([
            __DIR__ . '/../migrations' => database_path('migrations'),
        ], 'identity-service-client');

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
    }
}
