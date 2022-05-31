<?php

namespace SMSkin\IdentityServiceClient\Enums;

enum Scope: string
{
    case SYSTEM_CHANGE_SCOPES = 'system:change-scopes';
    case IDENTITY_SERVICE_LOGIN = 'identity-service:login';

}
