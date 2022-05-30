<?php

namespace SMSkin\IdentityServiceClient\Api\DTO\Identity;

use SMSkin\IdentityServiceClient\Api\Enums\CredentialType;
use Illuminate\Contracts\Support\Arrayable;

abstract class RCredential implements Arrayable
{
    public CredentialType $type;

    public function toArray(): array
    {
        return [
            'type' => $this->type
        ];
    }

    public function fromArray(array $data): self
    {
        $this->type = CredentialType::from($data['type']);
        return $this;
    }
}
