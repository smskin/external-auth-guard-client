<?php

namespace SMSkin\IdentityServiceClient\Api\Enums;

enum CredentialType: string
{
    case PHONE = 'PHONE';
    case EMAIL = 'EMAIL';
    case OAUTH_GITHUB = 'OAUTH_GITHUB';
}
