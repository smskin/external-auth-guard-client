<?php

namespace SMSkin\IdentityServiceClient\Api\DTO\Identity;

use SMSkin\IdentityServiceClient\Api\Enums\CredentialType;

class RPhoneCredential extends RCredential
{
    public CredentialType $type = CredentialType::PHONE;

    /**
     * @var string
     */
    public string $phone;

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'phone' => $this->phone
            ]
        );
    }

    public function fromArray(array $data): self
    {
        parent::fromArray($data);

        $this->phone = $data['phone'];
        return $this;
    }
}
