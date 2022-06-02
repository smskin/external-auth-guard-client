<?php

namespace SMSkin\IdentityServiceClient\Api\Requests\Auth\Email;

use GuzzleHttp\Exception\GuzzleException;
use SMSkin\IdentityServiceClient\Api\DTO\Auth\RJwt;
use SMSkin\IdentityServiceClient\Api\Requests\BaseRequest;

class Authorize extends BaseRequest
{
    /**
     * @param string $email
     * @param string $password
     * @param array $scopes
     * @return RJwt
     * @throws GuzzleException
     */
    public static function execute(string $email, string $password, array $scopes): RJwt
    {
        $client = self::getClient();
        $response = $client->post(
            '/identity-service/api/auth/email/authorize',
            [
                'email' => $email,
                'password' => $password,
                'scopes' => implode(',', $scopes)
            ]
        );

        $data = json_decode($response->getBody()->getContents(), true);
        return (new RJwt())->fromArray($data);
    }
}
