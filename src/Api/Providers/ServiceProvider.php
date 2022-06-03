<?php

namespace SMSkin\IdentityServiceClient\Api\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SMSkin\IdentityServiceClient\Api\Support\ApiClient;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->app->singleton(ApiClient::class, function () {
            return new ApiClient(
                config('identity-service-client.host.host'),
                config('identity-service-client.host.prefix')
            );
        });
    }
}
