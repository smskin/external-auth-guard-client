<?php

namespace SMSkin\IdentityServiceClient\Api\Requests\Auth\Jwt;

use GuzzleHttp\Exception\GuzzleException;
use SMSkin\IdentityServiceClient\Api\DTO\Auth\RJwt;
use SMSkin\IdentityServiceClient\Api\Requests\BaseRequest;
use SMSkin\IdentityServiceClient\Guard\Enums\Scope;

class Refresh extends BaseRequest
{
    /**
     * @param string $refreshToken
     * @param array<Scope>|null $scopes
     * @return RJwt
     * @throws GuzzleException
     */
    public function execute(string $refreshToken, ?array $scopes = null): RJwt
    {
        $client = $this->getClient();
        $data = [
            'token' => $refreshToken
        ];
        if ($scopes) {
            $data['scopes'] = implode(',', array_column($scopes, 'value'));
        }

        $response = $client->post('/identity-service/api/auth/jwt/refresh', $data);

        $data = json_decode($response->getBody()->getContents(), true);
        return (new RJwt())->fromArray($data);
    }
}
