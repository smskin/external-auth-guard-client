<?php

namespace SMSkin\IdentityServiceClient\Enums;

use Illuminate\Support\Collection;
use SMSkin\LaravelSupport\BaseEnum;
use SMSkin\LaravelSupport\Models\EnumItem;

class Scopes extends BaseEnum
{
    public const SYSTEM_CHANGE_SCOPES = 'system:change-scopes';
    public const IDENTITY_SERVICE_LOGIN = 'identity-service:login';
    public const IDENTITY_SERVICE_IMPERSONATE = 'identity-service:impersonate';
    public const IDENTITY_SERVICE_MANAGE_USER = 'identity-service:manage-user';

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
                ->setTitle('Авторизация в Identity service'),
            (new EnumItem())
                ->setId(self::IDENTITY_SERVICE_IMPERSONATE)
                ->setTitle('Функционал Impersonate в Identity service'),
            (new EnumItem())
                ->setId(self::IDENTITY_SERVICE_MANAGE_USER)
                ->setTitle('Управление пользователями в Identity service'),
        ]);
    }
}