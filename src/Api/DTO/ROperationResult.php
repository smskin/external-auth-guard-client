<?php

namespace SMSkin\IdentityServiceClient\Api\DTO;

use Illuminate\Contracts\Support\Arrayable;

class ROperationResult implements Arrayable
{
    public bool $result;

    public function toArray(): array
    {
        return [
            'result' => $this->result
        ];
    }

    public function fromArray(array $data): self
    {
        $this->result = $data['result'];
        return $this;
    }
}
