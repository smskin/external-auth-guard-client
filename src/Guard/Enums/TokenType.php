<?php

namespace SMSkin\IdentityServiceClient\Guard\Enums;

enum TokenType: string
{
    case ACCESS = 'ACCESS';
    case REFRESH = 'REFRESH';
}
