<?php

namespace SMSkin\IdentityServiceClient\Repository;

use SMSkin\IdentityServiceClient\Api\DTO\Identity\RIdentity;
use SMSkin\IdentityServiceClient\Models\Contracts\HasIdentity;

class UserRepository
{
    public static function create(RIdentity $identity): HasIdentity
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $user = self::getUser()::firstOrCreate([
            'identity_uuid' => $identity->uuid
        ]);
        $user->setIdentity($identity);
        return $user;
    }

    private static function getUser(): HasIdentity
    {
        return app(config('identity-service-client.classes.models.user'));
    }
}
