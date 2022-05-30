<?php

namespace SMSkin\IdentityServiceClient\Guard\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SMSkin\IdentityServiceClient\Guard\Http\Parser\Parser;
use SMSkin\IdentityServiceClient\Guard\Http\Parser\Parsers\AuthHeaders;
use SMSkin\IdentityServiceClient\Guard\Http\Parser\Parsers\Cookies;
use SMSkin\IdentityServiceClient\Guard\Http\Parser\Parsers\InputSource;
use SMSkin\IdentityServiceClient\Guard\Http\Parser\Parsers\QueryString;
use SMSkin\IdentityServiceClient\Guard\Http\Parser\Parsers\RouteParams;
use SMSkin\IdentityServiceClient\Guard\JWT;
use SMSkin\IdentityServiceClient\Guard\Support\TokenStorage;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->registerParsers();

        $this->app->singleton(JWT::class, fn($app) => (new JWT(
            $app[Parser::class]
        )));

        $this->registerStorage();
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

    private function registerStorage()
    {
        $this->app->singleton(TokenStorage::class, function($app){
            return new TokenStorage(
                $this->app['session.store'],
                $this->app['cookie'],
                $app['request'],
            );
        });
    }
}
