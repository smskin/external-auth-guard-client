<?php

namespace SMSkin\IdentityServiceClient\Guards\Jwt;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use SMSkin\IdentityServiceClient\Api\Requests\Identity\Logout;
use SMSkin\IdentityServiceClient\Guards\Jwt\Exceptions\JWTException;
use SMSkin\IdentityServiceClient\Guards\Jwt\Http\Parser\Parser;

class JWT
{
    protected Parser $parser;

    protected ?string $token = null;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function getToken(): ?string
    {
        if ($this->token === null) {
            try {
                $this->parseToken();
            } catch (JWTException) {
                $this->token = null;
            }
        }

        return $this->token;
    }

    /**
     * @return $this
     * @throws JWTException
     */
    public function parseToken(): self
    {
        if (!$token = $this->parser->parseToken()) {
            throw new JWTException('The token could not be parsed from the request');
        }

        return $this->setToken($token);
    }

    /**
     * @return void
     * @throws JWTException
     */
    public function invalidate()
    {
        $this->requireToken();

        try {
            Logout::execute($this->token);
        } catch (GuzzleException) {

        }
    }

    public function setToken(?string $token): JWT
    {
        $this->token = $token;
        return $this;
    }

    public function unsetToken(): self
    {
        $this->token = null;

        return $this;
    }

    public function setRequest(Request $request): self
    {
        $this->parser->setRequest($request);

        return $this;
    }

    /**
     * @return void
     * @throws JWTException
     */
    private function requireToken()
    {
        if (!$this->token) {
            throw new JWTException('A token is required');
        }
    }
}
