<?php

namespace SMSkin\IdentityServiceClient\Api\DTO\Identity;

use SMSkin\IdentityServiceClient\Api\Enums\CredentialType;
use SMSkin\LaravelSupport\Contracts\Arrayable;

abstract class RCredential implements Arrayable
{
    public CredentialType $type;

    public function toArray(): array
    {
        return [
            'type' => $this->type
        ];
    }

    public function fromArray(array $data): static
    {
        $this->type = CredentialType::from($data['type']);
        return $this;
    }
}
