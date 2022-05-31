<?php

namespace SMSkin\IdentityServiceClient\Guard\Jwt\Contracts;

use Illuminate\Contracts\Auth\Guard;
use SMSkin\IdentityServiceClient\Guard\Jwt\Exceptions\JWTException;
use SMSkin\IdentityServiceClient\Guard\Jwt\Exceptions\UserNotDefinedException;
use SMSkin\IdentityServiceClient\Guard\Jwt\JWT;
use SMSkin\IdentityServiceClient\Models\Contracts\HasIdentity;

interface BaseGuard extends Guard
{
    public function getToken(): ?JWT;

    /**
     * @return HasIdentity
     * @throws UserNotDefinedException
     */
    public function userOrFail(): HasIdentity;

    /**
     * @return void
     * @throws JWTException
     */
    public function logout(): void;

    public function attempt(array $credentials = [], $remember = false): bool;
}
