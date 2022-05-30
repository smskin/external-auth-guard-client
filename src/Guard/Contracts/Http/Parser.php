<?php

namespace SMSkin\IdentityServiceClient\Guard\Contracts\Http;

use Illuminate\Http\Request;

interface Parser
{
    public function parse(Request $request): ?string;
}
