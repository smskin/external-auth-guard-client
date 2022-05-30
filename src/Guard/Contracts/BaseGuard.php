<?php

namespace SMSkin\IdentityServiceClient\Guard\Contracts;

use SMSkin\IdentityServiceClient\Models\Contracts\HasIdentity;
use Illuminate\Contracts\Auth\Guard;
use SMSkin\IdentityServiceClient\Guard\Exceptions\JWTException;
use SMSkin\IdentityServiceClient\Guard\Exceptions\UserNotDefinedException;
use SMSkin\IdentityServiceClient\Guard\JWT;

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
