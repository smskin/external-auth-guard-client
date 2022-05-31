<?php

namespace SMSkin\IdentityServiceClient\Guard;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->app->register(Jwt\ServiceProvider::class);
        $this->app->register(Session\ServiceProvider::class);
    }
}
