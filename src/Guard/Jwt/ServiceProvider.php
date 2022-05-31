<?php

namespace SMSkin\IdentityServiceClient\Guard\Jwt;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SMSkin\IdentityServiceClient\Guard\Jwt\Http\Parser\Parser;
use SMSkin\IdentityServiceClient\Guard\Jwt\Http\Parser\Parsers\AuthHeaders;
use SMSkin\IdentityServiceClient\Guard\Jwt\Http\Parser\Parsers\Cookies;
use SMSkin\IdentityServiceClient\Guard\Jwt\Http\Parser\Parsers\InputSource;
use SMSkin\IdentityServiceClient\Guard\Jwt\Http\Parser\Parsers\QueryString;
use SMSkin\IdentityServiceClient\Guard\Jwt\Http\Parser\Parsers\RouteParams;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerParsers();

        $this->app->singleton(JWT::class, fn($app) => (new JWT(
            $app[Parser::class]
        )));

        $this->registerGuard();
    }


    private function registerParsers()
    {
        $this->app->singleton(Parser::class, function ($app) {
            $parser = new Parser(
                $app['request'],
                [
                    new AuthHeaders(),
                    new QueryString(),
                    new InputSource(),
                    new RouteParams(),
                    new Cookies(config('identity-service-client.parser.cookies.decrypt'))
                ]
            );

            $app->refresh('request', $parser, 'setRequest');

            return $parser;
        });
    }

    private function registerGuard()
    {
        $this->app['auth']->extend(config('identity-service-client.guards.jwt.driver.name'), function ($app, $name, array $config) {
            $guard = new Guard(
                app(JWT::class),
                $app['auth']->createUserProvider($config['provider']),
                $app['request'],
                $app['events'],
                config('identity-service-client.guards.jwt.name')
            );

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });

        config(['auth.guards.identity-service-client-jwt' => [
            'driver' => config('identity-service-client.guards.jwt.driver.name'),
            'provider' => 'users',
        ]]);
    }
}