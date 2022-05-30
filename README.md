# About Identity service library

Identity service is a service that allows you to organize authorization in a laravel application through a common remote
server. This allows you to organize a multi-service architecture with end-to-end authorization.

Identity service library consists of 2 parts:

- identity service - Master auth service (https://github.com/smskin/laravel-idenity-service)
- identity service client - this package. A client that allows application users to log in through a shared
  service

## Installation

1. Run `composer require smskin/laravel-identity-service-client`
2. Run `php artisan vendor:publish --tag=identity-service-client`
3. Configure identity service client with `identity-service-client.php` in config folder and environments
4. Change create user table migration file
5. Run `php artisan migrate`

## Migrations
User will be creating automatically if user open site with correct jwt. You must change users table for support nullable fields.

I usually remove all columns except id and dates because they are not needed (authorization happens through a remote server).
For example:
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});
```

## Environments
1. IDENTITY_SERVICE_CLIENT_HOST - public address of identity service (https://github.com/smskin/laravel-idenity-service)
2. IDENTITY_SERVICE_CLIENT_DEBUG - debug mode of auth gates
3. IDENTITY_SERVICE_CLIENT_API_TOKEN - secret key for admin functionality (admin api - https://github.com/smskin/laravel-idenity-service)

## Configuration
You can configure library with `identity-service-client.php` file.

- classes
  - models
    - user - Class of User model. You can override it with your user model class. You must implement `HasIdentity` contract and implement `IdentityTrait` trait
- scopes
  - initial - initial jwt scope for receive basic user data
  - uses - array of scopes, that uses by this service (the service in which this library is installed). For example service for administrate identity service uses the `Scope::IDENTITY_SERVICE_LOGIN` scope

Example of Users model:
```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use SMSkin\IdentityServiceClient\Models\Contracts\HasIdentity;
use SMSkin\IdentityServiceClient\Models\Traits\IdentityTrait;

class User extends Authenticatable implements HasIdentity
{
    use HasApiTokens, HasFactory, Notifiable;
    use IdentityTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'identity_uuid',
        'name',
    ];
}
```

## Using
This library register 2 guards:
- identity-service-client-jwt
- identity-service-client-session

You can use it with auth middleware (for example: `auth:identity-service-client-jwt`) or bind it's to already exists guards by `auth.php` config file.

For example:
```php
...
'guards' => [
    'web' => [
        'driver' => 'identity-service-client-session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'identity-service-client-jwt',
        'provider' => 'users',
    ],
],
...
```

# Logic of authorization with unknown available scopes
1. Gate tries login with email credentials and initial scope (`identity-service-client.scopes.initial`)
2. Gate receives JWT
3. Gate calls `/identity-service/api/identity/scopes` method for receive available user scopes
4. Gate filters available scopes by uses scopes (`identity-service-client.scopes.uses`)
5. Gate calls `/identity-service/api/auth/jwt/refresh` method for refresh the token with uses scopes
6. Gate receives correct JWT for use in service