<?php

namespace SMSkin\IdentityServiceClient\Api\DTO\Auth;

use Illuminate\Contracts\Support\Arrayable;

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

    public function fromArray(array $data): self
    {
        $this->accessToken = (new RToken())->fromArray($data['accessToken']);
        $this->refreshToken = (new RToken())->fromArray($data['refreshToken']);
        return $this;
    }
}
