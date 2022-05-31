<?php

namespace SMSkin\IdentityServiceClient\Guard\Jwt\Http\Parser\Parsers;

use Illuminate\Http\Request;
use SMSkin\IdentityServiceClient\Guard\Jwt\Contracts\Http\Parser;
use SMSkin\IdentityServiceClient\Guard\Jwt\Http\Parser\Traits\KeyTrait;

class RouteParams implements Parser
{
    use KeyTrait;

    public function parse(Request $request): ?string
    {
        $route = $request->route();

        // Route may not be an instance of Illuminate\Routing\Route
        // (it's an array in Lumen <5.2) or not exist at all
        // (if the request was never dispatched)
        if (is_callable([$route, 'parameter'])) {
            return $route->parameter($this->key);
        }
        return null;
    }
}
