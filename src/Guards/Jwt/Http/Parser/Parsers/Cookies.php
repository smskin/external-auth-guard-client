<?php

namespace SMSkin\IdentityServiceClient\Guards\Jwt\Http\Parser\Parsers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use SMSkin\IdentityServiceClient\Guards\Jwt\Contracts\Http\Parser;
use SMSkin\IdentityServiceClient\Guards\Jwt\Exceptions\TokenInvalidException;
use SMSkin\IdentityServiceClient\Guards\Jwt\Http\Parser\Traits\KeyTrait;

class Cookies implements Parser
{
    use KeyTrait;

    private bool $decrypt;

    public function __construct($decrypt = true)
    {
        $this->decrypt = $decrypt;
    }

    /**
     * @param Request $request
     * @return string|null
     * @throws TokenInvalidException
     */
    public function parse(Request $request): ?string
    {
        if ($this->decrypt && $request->hasCookie($this->key)) {
            try {
                return Crypt::decrypt($request->cookie($this->key));
            } catch (DecryptException) {
                throw new TokenInvalidException('Token has not decrypted successfully.');
            }
        }

        return $request->cookie($this->key);
    }
}
