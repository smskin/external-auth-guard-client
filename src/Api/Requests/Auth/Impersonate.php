<?php

namespace SMSkin\IdentityServiceClient\Api\Requests\Auth;

use GuzzleHttp\Exception\GuzzleException;
use SMSkin\IdentityServiceClient\Api\DTO\Auth\RJwt;
use SMSkin\IdentityServiceClient\Api\Requests\BaseRequest;

class Impersonate extends BaseRequest
{
    /**
     * @param string $uuid
     * @return RJwt
     * @throws GuzzleException
     */
    public static function execute(string $uuid): RJwt
    {
        $client = self::getClient();
        $response = $client->post(
            '/identity-service/api/auth/impersonate',
            [
                'uuid' => $uuid
            ],
            [
                'X-API-TOKEN' => config('identity-service-client.api.token')
            ]
        );

        $data = json_decode($response->getBody()->getContents(), true);
        return (new RJwt())->fromArray($data);
    }
}
