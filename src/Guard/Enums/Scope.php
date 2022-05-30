<?php

namespace SMSkin\IdentityServiceClient\Guard\Enums;

enum Scope: string
{
    case SYSTEM_CHANGE_SCOPES = 'system:change-scopes';
    case IDENTITY_SERVICE_LOGIN = 'identity-service:login';

}
