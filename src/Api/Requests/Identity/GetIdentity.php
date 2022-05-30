<?php

namespace SMSkin\IdentityServiceClient\Api\Requests\Identity;

use Illuminate\Support\Facades\Cache;
use SMSkin\IdentityServiceClient\Api\DTO\Identity\RIdentity;
use SMSkin\IdentityServiceClient\Api\Requests\BaseRequest;
use GuzzleHttp\Exception\GuzzleException;

class GetIdentity extends BaseRequest
{
    /**
     * @param string $token
     * @return RIdentity
     * @throws GuzzleException
     */
    public function execute(string $token): RIdentity
    {
        $identity = $this->getIdentityFromCache($token);
        if ($identity) {
            return $identity;
        }

        $client = $this->getClient();
        $client->setAccessToken($token);
        $response = $client->get(
            '/identity-service/api/identity'
        );

        $data = json_decode($response->getBody()->getContents(), true);
        $identity = (new RIdentity)->fromArray($data);
        $this->putIdentityToCache($token, $identity);
        return $identity;
    }

    private function getCacheKey(string $token): string
    {
        return 'api_request_cache_' . md5(static::class . '_' . $token);
    }

    private function getIdentityFromCache(string $token): ?RIdentity
    {
        $data = Cache::get($this->getCacheKey($token));
        if (!$data) {
            return null;
        }

        return (new RIdentity)->fromArray($data);
    }

    private function putIdentityToCache(string $token, RIdentity $identity): void
    {
        Cache::put($this->getCacheKey($token), $identity->toArray(), 1);
    }
}
