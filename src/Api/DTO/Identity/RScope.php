<?php

namespace SMSkin\IdentityServiceClient\Api\DTO\Identity;

use SMSkin\LaravelSupport\Contracts\Arrayable;

class RScope implements Arrayable
{
    public string $name;
    public string $value;

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value
        ];
    }

    public function fromArray(array $data): static
    {
        $this->name = $data['name'];
        $this->value = $data['value'];
        return $this;
    }
}