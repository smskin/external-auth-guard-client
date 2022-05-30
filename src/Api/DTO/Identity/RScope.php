<?php

namespace SMSkin\IdentityServiceClient\Api\DTO\Identity;

use Illuminate\Contracts\Support\Arrayable;

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

    public function fromArray(array $data): self
    {
        $this->name = $data['name'];
        $this->value = $data['value'];
        return $this;
    }
}