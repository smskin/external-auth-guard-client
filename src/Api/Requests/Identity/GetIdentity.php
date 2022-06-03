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
    public static function execute(string $token): RIdentity
    {
        $identity = self::getIdentityFromCache($token);
        if ($identity) {
            return $identity;
        }

        $client = self::getClient();
        $client->setAccessToken($token);
        $response = $client->get(
            '/api/identity'
        );

        $data = json_decode($response->getBody()->getContents(), true);
        $identity = (new RIdentity)->fromArray($data);
        self::putIdentityToCache($token, $identity);
        return $identity;
    }

    private static function getCacheKey(string $token): string
    {
        return 'api_request_cache_' . md5(static::class . '_' . $token);
    }

    private static function getIdentityFromCache(string $token): ?RIdentity
    {
        $data = Cache::get(self::getCacheKey($token));
        if (!$data) {
            return null;
        }

        return (new RIdentity)->fromArray($data);
    }

    private static function putIdentityToCache(string $token, RIdentity $identity): void
    {
        Cache::put(self::getCacheKey($token), $identity->toArray(), 1);
    }
}
