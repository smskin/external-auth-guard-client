<?php

namespace SMSkin\IdentityServiceClient\Api\DTO\Identity;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

class RIdentity implements Arrayable
{
    public string $uuid;

    public string $name;

    public array $scopes;

    public ?Carbon $createdAt;

    public ?Carbon $updatedAt;

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'scopes' => $this->scopes,
            'createdAt' => $this->createdAt?->toIso8601String(),
            'updatedAt' => $this->updatedAt?->toIso8601String()
        ];
    }

    public function fromArray(array $data): self
    {
        $this->uuid = $data['uuid'];
        $this->name = $data['name'];
        $this->scopes = $data['scopes'];
        $this->createdAt = $data['createdAt'] ? Carbon::make($data['createdAt']) : null;
        $this->updatedAt = $data['updatedAt'] ? Carbon::make($data['updatedAt']) : null;
        return $this;
    }
}
