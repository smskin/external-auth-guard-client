<?php

namespace SMSkin\IdentityServiceClient\Traits;

use SMSkin\IdentityServiceClient\Models\Contracts\HasIdentity;

trait ClassFromConfig
{
    public static function getUserModelClass(): string
    {
        return config('identity-service-client.classes.models.user');
    }

    public static function getUserModel(): HasIdentity
    {
        return app(self::getUserModelClass());
    }
}