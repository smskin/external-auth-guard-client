<?php

namespace SMSkin\IdentityServiceClient\Enums;

use Illuminate\Support\Collection;
use SMSkin\IdentityServiceClient\Enums\Models\ScopeGroup;
use SMSkin\LaravelSupport\BaseEnum;

class ScopeGroups extends BaseEnum
{
    public const SYSTEM = 'system';
    public const IDENTITY_SERVICE = 'identity-service';

    /**
     * @return Collection<ScopeGroup>
     */
    public static function items(): Collection
    {
        return collect([
            (new ScopeGroup())
                ->setId(self::SYSTEM)
                ->setTitle('Системные')
                ->setScopes(collect([
                    Scopes::getById(Scopes::SYSTEM_CHANGE_SCOPES),
                ])),
            (new ScopeGroup())
                ->setId(self::IDENTITY_SERVICE)
                ->setTitle('Identity service')
                ->setScopes(collect([
                    Scopes::getById(Scopes::IDENTITY_SERVICE_LOGIN)
                ]))
        ]);
    }
}