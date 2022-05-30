<?php

namespace SMSkin\IdentityServiceClient\Guard\Contracts;

use SMSkin\IdentityServiceClient\Api\DTO\Auth\RToken;

interface Storage
{
    public function putToken(RToken $token): void;
    public function tokenExists(): bool;
    public function getToken(): ?RToken;
    public function removeToken(): void;
}
