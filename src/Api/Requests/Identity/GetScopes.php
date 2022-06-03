<?php

namespace SMSkin\IdentityServiceClient\Api\Requests\Identity;

use GuzzleHttp\Exception\GuzzleException;
use SMSkin\IdentityServiceClient\Api\DTO\Identity\RScope;
use SMSkin\IdentityServiceClient\Api\Requests\BaseRequest;

class GetScopes extends BaseRequest
{
    /**
     * @param string $token
     * @return array<RScope>
     * @throws GuzzleException
     */
    public static function execute(string $token): array
    {
        $client = self::getClient();
        $client->setAccessToken($token);
        $response = $client->get(
            '/api/identity/scopes'
        );

        $data = json_decode($response->getBody()->getContents(), true);
        $scopes = [];
        foreach ($data as $item) {
            $scopes[] = (new RScope())->fromArray($item);
        }
        return $scopes;
    }
}