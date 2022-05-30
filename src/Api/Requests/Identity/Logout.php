<?php

namespace SMSkin\IdentityServiceClient\Api\Requests\Identity;

use GuzzleHttp\Exception\GuzzleException;
use SMSkin\IdentityServiceClient\Api\Requests\BaseRequest;

class Logout extends BaseRequest
{
    /**
     * @param string $token
     * @throws GuzzleException
     */
    public function execute(string $token): void
    {
        $client = $this->getClient();
        $client->setAccessToken($token);
        $client->get(
            '/identity-service/api/identity/logout'
        );
    }
}
