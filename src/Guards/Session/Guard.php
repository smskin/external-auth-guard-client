<?php

namespace SMSkin\IdentityServiceClient\Guards\Session;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;
use Illuminate\Contracts\Session\Session;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Log;
use SMSkin\IdentityServiceClient\Api\DTO\Auth\RJwt;
use SMSkin\IdentityServiceClient\Api\DTO\Auth\RToken;
use SMSkin\IdentityServiceClient\Api\DTO\Identity\RIdentity;
use SMSkin\IdentityServiceClient\Api\DTO\Identity\RScope;
use SMSkin\IdentityServiceClient\Api\Requests\Auth\Email\Authorize as AuthorizeByEmail;
use SMSkin\IdentityServiceClient\Api\Requests\Auth\Email\Validate as ValidateByEmail;
use SMSkin\IdentityServiceClient\Api\Requests\Auth\Impersonate;
use SMSkin\IdentityServiceClient\Api\Requests\Auth\Jwt\Refresh;
use SMSkin\IdentityServiceClient\Api\Requests\Identity\GetIdentity;
use SMSkin\IdentityServiceClient\Api\Requests\Identity\GetScopes;
use SMSkin\IdentityServiceClient\Api\Requests\Identity\Logout;
use SMSkin\IdentityServiceClient\Guards\Session\Exceptions\MutexException;
use SMSkin\IdentityServiceClient\Guards\Session\Exceptions\UnsupportedGuardMethod;
use SMSkin\IdentityServiceClient\Guards\Session\Support\TokenStorage;
use SMSkin\IdentityServiceClient\Models\Contracts\HasIdentity;
use SMSkin\IdentityServiceClient\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use SyncMutex;
use function app;
use function config;

class Guard extends SessionGuard
{
    /**
     * @var HasIdentity
     */
    protected $user;

    protected ?RToken $accessToken = null;

    protected string $id;

    protected TokenStorage $storage;

    protected bool $debug = false;

    public function __construct($name, UserProvider $provider, Session $session, CookieJar $cookie, Request $request = null, bool $debug = false)
    {
        $this->id = uniqid();
        $this->cookie = $cookie;
        $this->debug = $debug;
        $this->storage = app(TokenStorage::class);
        parent::__construct($name, $provider, $session, $request);

        if ($this->request) {
            $this->logDebug('Request. Method: ' . $this->request->getMethod() . ', Url: ' . $this->request->getUri());
        }
    }

    /**
     * @return HasIdentity|null
     * @throws MutexException
     */
    public function user(): ?HasIdentity
    {
        if ($this->loggedOut) {
            return null;
        }

        if (!is_null($this->user)) {
            return $this->user;
        }

        $user = $this->getUserByToken();
        if ($user) {
            return $user;
        }

        $this->logDebug('guest');
        $this->storage->drop();
        $this->loggedOut = true;
        return null;
    }

    /**
     * @return HasIdentity|null
     * @throws MutexException
     * @throws Exception
     */
    private function getUserByToken(): ?HasIdentity
    {
        $key = md5(static::class . '@getUserByToken') . '_' . $this->session->getId();
        $mutex = new SyncMutex($key);
        if (!$mutex->lock(10000)) {
            throw new MutexException('Can\'t lock (' . static::class . '@getUserByToken) within 10 seconds');
        }

        $this->logDebug('Mutex: lock by key ' . $key);

        try {
            if (!$this->storage->exists()) {
                return null;
            }

            $user = $this->getUserByAccessToken();
            if ($user) {
                return $user;
            }

            $user = $this->getUserByRefreshToken();
            if ($user) {
                return $user;
            }
            return null;
        } finally {
            $mutex->unlock();
            $this->logDebug('Mutex: unlock by key ' . $key);
        }
    }

    /**
     * @param array $credentials
     * @return bool
     * @throws MutexException
     */
    public function once(array $credentials = []): bool
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'];

        $this->fireAttemptEvent($credentials);

        if ($email) {
            $jwt = $this->getJwtByEmailCredentials($email, $password);
            if (!$jwt) {
                return false;
            }
            $identity = $this->getRemoteIdentityByToken($jwt->accessToken);
            if (!$identity) {
                return false;
            }
            $user = $this->getUserByIdentity($identity);
            $this->setUser($user);
            return true;
        }

        return false;
    }

    public function validate(array $credentials = []): bool
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'];

        if ($email) {
            return $this->validateByEmail($email, $password);
        }
        return false;
    }

    /**
     * @param array $credentials
     * @param $remember
     * @return bool
     * @throws MutexException
     * @noinspection PhpMissingParamTypeInspection
     */
    public function attempt(array $credentials = [], $remember = false): bool
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'];

        $this->fireAttemptEvent($credentials, $remember);

        if ($email) {
            $jwt = $this->getJwtByEmailCredentials($email, $password);
            if (!$jwt) {
                return false;
            }
            $identity = $this->getRemoteIdentityByToken($jwt->accessToken);
            if (!$identity) {
                return false;
            }
            $user = $this->getUserByIdentity($identity);
            $this->updateUser($user);
            $this->storage->put($jwt, $remember);
            $this->fireLoginEvent($user, $remember);
            return true;
        }
        return false;
    }

    /**
     * @param array $credentials
     * @param $callbacks
     * @param $remember
     * @return bool
     * @throws MutexException
     * @noinspection PhpMissingParamTypeInspection
     */
    public function attemptWhen(array $credentials = [], $callbacks = null, $remember = false): bool
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'];

        $this->fireAttemptEvent($credentials, $remember);

        if ($email) {
            $jwt = $this->getJwtByEmailCredentials($email, $password);
            if (!$jwt) {
                return false;
            }
            $identity = $this->getRemoteIdentityByToken($jwt->accessToken);
            if (!$identity) {
                return false;
            }
            $user = $this->getUserByIdentity($identity);
            if ($this->shouldLogin($callbacks, $user)) {
                $this->updateUser($user);
                $this->storage->put($jwt, $remember);
                $this->fireLoginEvent($user, $remember);
                return true;
            }
        }
        return false;
    }

    public function loginUsingId($id, $remember = false): bool|HasIdentity
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $user = User::first($id);
        if ($user) {
            $this->login($user, $remember);
            return $user;
        }
        return false;
    }

    public function login(HasIdentity|Authenticatable $user, $remember = false): void
    {
        $jwt = $this->getJwtByUser($user);
        if (!$jwt) {
            return;
        }
        $identity = $this->getRemoteIdentityByToken($jwt->accessToken);
        if (!$identity) {
            return;
        }
        $user = $this->getUserByIdentity($identity);
        $this->updateUser($user);
        $this->storage->put($jwt, $remember);
        $this->fireLoginEvent($user, $remember);
    }

    public function logout(): void
    {
        if ($this->loggedOut) {
            return;
        }

        if (is_null($this->user)) {
            return;
        }

        try {
            Logout::execute($this->accessToken->value);
        } catch (GuzzleException $exception) {
            $this->logGuzzleException('logout', $exception);
        }

        $this->setAccessToken(null);
        $this->storage->drop();
        $this->logDebug('logout');
        parent::logout();
    }

    /**
     * @param string $password
     * @param string $attribute
     * @return HasIdentity|null
     * @throws UnsupportedGuardMethod
     */
    public function logoutOtherDevices($password, $attribute = 'password'): HasIdentity|null
    {
        throw new UnsupportedGuardMethod();
    }

    private function validateByEmail(string $email, string $password): bool
    {
        try {
            return ValidateByEmail::execute($email, $password);
        } catch (GuzzleException $exception) {
            $this->logGuzzleException('validateByEmail - 1', $exception);
            return false;
        }
    }

    private function updateUser(HasIdentity $user): void
    {
        $this->updateSession($user->getAuthIdentifier());
        $this->setUser($user);
    }

    protected function updateSession($id)
    {
        $lastId = $this->session->get($this->getName());
        if (!$lastId || $lastId != $id) {
            $this->session->put($this->getName(), $id);
            $this->session->migrate(true);
        }
    }

    private function getJwtByUser(HasIdentity $user): ?RJwt
    {
        try {
            /** @noinspection PhpUndefinedFieldInspection */
            return Impersonate::execute($user->identity_uuid);
        } catch (GuzzleException $exception) {
            $this->logGuzzleException('attemptByEmail - 1', $exception);
            return null;
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @return RJwt|null
     * @throws MutexException
     */
    private function getJwtByEmailCredentials(string $email, string $password): ?RJwt
    {
        try {
            $jwt = AuthorizeByEmail::execute($email, $password, [config('identity-service-client.scopes.initial')]);
            $scopes = $this->getScopes($jwt->accessToken);
            return $this->getJwtByRefreshToken($jwt->refreshToken, $scopes);
        } catch (GuzzleException $exception) {
            $this->logGuzzleException('getJwtByEmailCredentials', $exception);
            return null;
        }
    }

    private function getScopes(RToken $token): array
    {
        $uses = config('identity-service-client.scopes.uses');
        $available = array_column($this->getAvailableScopes($token), 'value');

        $scopes = [];
        foreach ($uses as $scope) {
            if (in_array($scope, $available)) {
                $scopes[] = $scope;
            }
        }
        return $scopes;
    }

    /**
     * @param RToken $token
     * @return array<RScope>|null
     */
    private function getAvailableScopes(RToken $token): ?array
    {
        try {
            return GetScopes::execute($token->value);
        } catch (GuzzleException $exception) {
            $this->logGuzzleException('getAvailableScopes', $exception);
            return null;
        }
    }

    private function getRemoteIdentityByToken(RToken $token): ?RIdentity
    {
        try {
            $identity = GetIdentity::execute($token->value);
        } catch (GuzzleException $exception) {
            $this->logGuzzleException('getRemoteIdentityByToken', $exception);
            return null;
        }
        return $identity;
    }

    private function getUserByIdentity(RIdentity $identity): HasIdentity
    {
        return UserRepository::create($identity);
    }

    private function logGuzzleException(string $method, GuzzleException $exception)
    {
        if (!$this->debug) {
            return;
        }
        Log::error('[' . static::class . '][' . $this->id . '][Guzzle][' . $method . ']: ' . $exception->getMessage(), ['exception' => $exception]);
    }

    private function logDebug(string $message): void
    {
        if (!$this->debug) {
            return;
        }
        Log::debug('[' . static::class . '][' . $this->id . ']: ' . $message);
    }

    /**
     * @return HasIdentity|null
     */
    private function getUserByAccessToken(): ?HasIdentity
    {
        $token = $this->storage->getAccessToken();
        if (!$token) {
            $this->logDebug('Access token not exists');
            return null;
        }

        if ($token->isExpired()) {
            $this->logDebug('Access token is expired');
            return null;
        }

        $identity = $this->getRemoteIdentityByToken($token);
        if (!$identity) {
            $this->logDebug('Identity not received by access token');
            return null;
        }

        $user = $this->getUserByIdentity($identity);
        $this->logDebug('Received user by access token');
        $this->setAccessToken($token);
        $this->updateUser($user);
        $this->fireAuthenticatedEvent($this->user);
        return $user;
    }

    /**
     * @return HasIdentity|null
     * @throws MutexException
     */
    private function getUserByRefreshToken(): ?HasIdentity
    {
        $token = $this->storage->getRefreshToken();
        if (!$token) {
            $this->logDebug('Refresh token not exists');
            return null;
        }

        if ($token->isExpired()) {
            $this->logDebug('Refresh token is expired');
            return null;
        }

        $jwt = $this->getJwtByRefreshToken($token);
        if (!$jwt) {
            $this->logDebug('Jwt not received by refresh token');
            return null;
        }

        $this->storage->put($jwt, true);
        return $this->getUserByAccessToken();
    }

    /**
     * @param RToken $token
     * @param array|null $scopes
     * @return RJwt|null
     * @throws MutexException
     * @throws Exception
     */
    private function getJwtByRefreshToken(RToken $token, ?array $scopes = null): ?RJwt
    {
        $key = md5(static::class . '_' . $token->value);
        $mutex = new SyncMutex($key);
        if (!$mutex->lock(10000)) {
            throw new MutexException('Can\'t lock (' . static::class . '@getJwtByRefreshToken) within 10 seconds');
        }

        try {
            return Refresh::execute($token->value, $scopes);
        } catch (GuzzleException $exception) {
            $this->logGuzzleException('getJwtByRefreshToken', $exception);
            return null;
        } finally {
            $mutex->unlock();
        }
    }

    /**
     * @param RToken|null $accessToken
     * @return void
     */
    private function setAccessToken(?RToken $accessToken): void
    {
        $this->accessToken = $accessToken;
    }
}
