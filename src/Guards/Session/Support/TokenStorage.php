<?php

namespace SMSkin\IdentityServiceClient\Guards\Session\Support;

use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use SMSkin\IdentityServiceClient\Api\DTO\Auth\RJwt;
use SMSkin\IdentityServiceClient\Api\DTO\Auth\RToken;
use Symfony\Component\HttpFoundation\Request;

class TokenStorage
{
    protected string $key;

    public function __construct(protected Session $session, protected CookieJar $cookie, protected ?Request $request = null)
    {
        $this->key = $this->getCacheKey();
    }

    public function exists(): bool
    {
        return Cache::has($this->key);
    }

    public function get(): ?RJwt
    {
        $data = Cache::get($this->key);
        if (!$data) {
            return null;
        }
        return (new RJwt())->fromArray($data);
    }

    public function getAccessToken(): ?RToken
    {
        $jwt = $this->get();
        if (!$jwt) {
            return null;
        }
        return $jwt->accessToken;
    }

    public function getRefreshToken(): ?RToken
    {
        $jwt = $this->get();
        if (!$jwt) {
            return null;
        }
        return $jwt->refreshToken;
    }

    public function put(RJwt $jwt, bool $remember = false)
    {
        $expireAt = $remember ? $jwt->refreshToken->expireAt : $jwt->accessToken->expireAt;
        Cache::put($this->key, $jwt->toArray(), $expireAt);
    }

    public function drop()
    {
        Cache::forget($this->key);
    }

    private function getCacheKey(): string
    {
        if ($this->cookieExists()) {
            $key = $this->getKeyFromCookie();
            if (!is_null($key)) {
                return $key;
            }
        }

        $key = $this->generateCacheKey();
        $this->putKeyToCookie($key);
        return $key;
    }

    private function cookieExists(): bool
    {
        if (!$this->request) {
            return false;
        }
        return $this->request->cookies->has($this->getCookieName());
    }

    private function getKeyFromCookie(): ?string
    {
        if (!$this->request) {
            return null;
        }
        $cookie = $this->request->cookies->get($this->getCookieName());
        if (is_null($cookie)) {
            return null;
        }
        try {
            $cookie = Crypt::decryptString($cookie);
            return explode('|', $cookie)[1];
        } catch (DecryptException) {

        }
        return $cookie;
    }

    private function putKeyToCookie(string $key)
    {
        $cookie = $this->cookie->make($this->getCookieName(), $key, 2628000);
        $this->cookie->queue($cookie);
    }

    private function getCookieName(): string
    {
        return 'identity-service-client-token_' . sha1(static::class);
    }

    private function generateCacheKey(): string
    {
        return sha1(static::class . $this->session->getId() ?? uniqid());
    }
}
