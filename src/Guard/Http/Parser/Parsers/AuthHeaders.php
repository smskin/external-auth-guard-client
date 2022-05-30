<?php

namespace SMSkin\IdentityServiceClient\Guard\Http\Parser\Parsers;

use Illuminate\Http\Request;
use SMSkin\IdentityServiceClient\Guard\Contracts\Http\Parser;

class AuthHeaders implements Parser
{
    protected string $header = 'authorization';

    protected string $prefix = 'bearer';

    protected function fromAltHeaders(Request $request): ?string
    {
        return $request->server->get('HTTP_AUTHORIZATION') ?: $request->server->get('REDIRECT_HTTP_AUTHORIZATION');
    }

    public function parse(Request $request): ?string
    {
        $header = $request->headers->get($this->header) ?: $this->fromAltHeaders($request);

        if ($header) {
            $start = strlen($this->prefix);

            return trim(substr($header, $start));
        }
        return null;
    }

    public function setHeaderName(string $headerName): self
    {
        $this->header = $headerName;

        return $this;
    }

    public function setHeaderPrefix(string $headerPrefix): self
    {
        $this->prefix = $headerPrefix;

        return $this;
    }
}
