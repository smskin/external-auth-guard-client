<?php

namespace SMSkin\IdentityServiceClient\Api\DTO\Identity;

use SMSkin\IdentityServiceClient\Api\Enums\CredentialType;

class ROAuthGithubCredential extends RCredential
{
    public CredentialType $type = CredentialType::OAUTH_GITHUB;
}