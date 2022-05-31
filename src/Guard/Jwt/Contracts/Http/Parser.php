<?php

namespace SMSkin\IdentityServiceClient\Guard\Jwt\Contracts\Http;

use Illuminate\Http\Request;

interface Parser
{
    public function parse(Request $request): ?string;
}
