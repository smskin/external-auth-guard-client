<?php

namespace SMSkin\IdentityServiceClient;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

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
        $this->registerConfig();

        $this->app->register(Api\Providers\ServiceProvider::class);
        $this->app->register(Guards\ServiceProvider::class);
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

    private function registerMigrations()
    {
        $this->publishes([
            __DIR__ . '/../migrations' => database_path('migrations'),
        ], 'identity-service-client');

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
    }
}
