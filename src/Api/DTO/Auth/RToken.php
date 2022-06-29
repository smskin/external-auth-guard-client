<?php

namespace SMSkin\IdentityServiceClient\Api\DTO\Auth;

use Carbon\Carbon;
use SMSkin\LaravelSupport\Contracts\Arrayable;

class RToken implements Arrayable
{
    public string $value;

    public int $expiresIn;

    public Carbon $expireAt;

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'expiresIn' => $this->expiresIn,
            'expireAt' => $this->expireAt->toIso8601String()
        ];
    }

    public function fromArray(array $data): static
    {
        $this->value = $data['value'];
        $this->expiresIn = $data['expiresIn'];
        $this->expireAt = Carbon::make($data['expireAt']);
        return $this;
    }

    public function isExpired(): bool
    {
        return now()->addSeconds(10)->gte($this->expireAt);
    }
}
