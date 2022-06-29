<?php

namespace SMSkin\IdentityServiceClient\Api\DTO\Auth;

use SMSkin\LaravelSupport\Contracts\Arrayable;

class RJwt implements Arrayable
{
    public RToken $accessToken;
    public RToken $refreshToken;

    public function toArray(): array
    {
        return [
            'accessToken' => $this->accessToken->toArray(),
            'refreshToken' => $this->refreshToken->toArray()
        ];
    }

    public function fromArray(array $data): static
    {
        $this->accessToken = (new RToken())->fromArray($data['accessToken']);
        $this->refreshToken = (new RToken())->fromArray($data['refreshToken']);
        return $this;
    }
}
