<?php

namespace SMSkin\IdentityServiceClient\Guard\Jwt\Http\Parser\Traits;

trait KeyTrait
{
    protected string $key = 'token';

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
