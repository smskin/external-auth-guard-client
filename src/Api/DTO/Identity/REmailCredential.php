<?php

namespace SMSkin\IdentityServiceClient\Api\DTO\Identity;

use SMSkin\IdentityServiceClient\Api\Enums\CredentialType;

class REmailCredential extends RCredential
{
    public CredentialType $type = CredentialType::EMAIL;

    public string $email;

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'email' => $this->email
            ]
        );
    }

    public function fromArray(array $data): self
    {
        parent::fromArray($data);

        $this->email = $data['email'];
        return $this;
    }
}
