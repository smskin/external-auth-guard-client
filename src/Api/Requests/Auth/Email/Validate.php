<?php

namespace SMSkin\IdentityServiceClient\Api\Requests\Auth\Email;

use SMSkin\IdentityServiceClient\Api\DTO\ROperationResult;
use SMSkin\IdentityServiceClient\Api\Requests\BaseRequest;
use GuzzleHttp\Exception\GuzzleException;

class Validate extends BaseRequest
{
    /**
     * @param string $email
     * @param string $password
     * @return bool
     * @throws GuzzleException
     */
    public function execute(string $email, string $password): bool
    {
        $client = $this->getClient();
        $response = $client->post(
            '/identity-service/api/auth/email/validate',
            [
                'email' => $email,
                'password' => $password
            ]
        );

        $data = json_decode($response->getBody()->getContents(), true);
        $model = (new ROperationResult)->fromArray($data);
        return $model->result;
    }
}
