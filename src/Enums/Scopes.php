<?php

namespace SMSkin\IdentityServiceClient\Enums;

use Illuminate\Support\Collection;
use SMSkin\LaravelSupport\BaseEnum;
use SMSkin\LaravelSupport\Models\EnumItem;

class Scopes extends BaseEnum
{
    public const SYSTEM_CHANGE_SCOPES = 'system:change-scopes';
    public const IDENTITY_SERVICE_LOGIN = 'identity-service:login';

    /**
     * @return Collection<EnumItem>
     */
    public static function items(): Collection
    {
        return collect([
            (new EnumItem())
                ->setId(self::SYSTEM_CHANGE_SCOPES)
                ->setTitle('Повышение привелегий'),
            (new EnumItem())
                ->setId(self::IDENTITY_SERVICE_LOGIN)
                ->setTitle('Авторизация в Identity service')
        ]);
    }
}