<?php

namespace SMSkin\IdentityServiceClient\Repository;

use SMSkin\IdentityServiceClient\Api\DTO\Identity\RIdentity;
use SMSkin\IdentityServiceClient\Models\Contracts\HasIdentity;
use SMSkin\IdentityServiceClient\Traits\ClassFromConfig;

class UserRepository
{
    use ClassFromConfig;

    public static function create(RIdentity $identity): HasIdentity
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $user = self::getUserModel()::firstOrCreate([
            'identity_uuid' => $identity->uuid
        ]);
        $user->setIdentity($identity);
        return $user;
    }
}
