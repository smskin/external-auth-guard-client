<?php

namespace SMSkin\IdentityServiceClient\Api\DTO;

use SMSkin\LaravelSupport\Contracts\Arrayable;

class ROperationResult implements Arrayable
{
    public bool $result;

    public function toArray(): array
    {
        return [
            'result' => $this->result
        ];
    }

    public function fromArray(array $data): static
    {
        $this->result = $data['result'];
        return $this;
    }
}
