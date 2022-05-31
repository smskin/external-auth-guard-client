<?php

namespace SMSkin\IdentityServiceClient\Guard\Jwt\Http\Parser;

use Illuminate\Http\Request;

class Parser
{
    private array $chain;

    protected Request $request;

    public function __construct(Request $request, array $chain = [])
    {
        $this->request = $request;
        $this->chain = $chain;
    }

    public function parseToken(): ?string
    {
        foreach ($this->chain as $parser) {
            if ($response = $parser->parse($this->request)) {
                return $response;
            }
        }
        return null;
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }
}
