<?php

namespace SMSkin\IdentityServiceClient\Api\Requests\Auth\Email;

use BackedEnum;
use GuzzleHttp\Exception\GuzzleException;
use SMSkin\IdentityServiceClient\Api\DTO\Auth\RJwt;
use SMSkin\IdentityServiceClient\Api\Requests\BaseRequest;
use SMSkin\IdentityServiceClient\Enums\Scope;

class Authorize extends BaseRequest
{
    /**
     * @param string $email
     * @param string $password
     * @param array<Scope> $scopes
     * @return RJwt
     * @throws GuzzleException
     */
    public function execute(string $email, string $password, array $scopes): RJwt
    {
        $client = $this->getClient();
        $response = $client->post(
            '/identity-service/api/auth/email/authorize',
            [
                'email' => $email,
                'password' => $password,
                'scopes' => collect($scopes)->map(function (BackedEnum $scope) {
                    return $scope->value;
                })->implode(',')
            ]
        );

        $data = json_decode($response->getBody()->getContents(), true);
        return (new RJwt())->fromArray($data);
    }
}
