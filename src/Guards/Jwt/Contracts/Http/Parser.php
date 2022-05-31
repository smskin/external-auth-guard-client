<?php

namespace SMSkin\IdentityServiceClient\Guards\Jwt\Contracts\Http;

use Illuminate\Http\Request;

interface Parser
{
    public function parse(Request $request): ?string;
}
