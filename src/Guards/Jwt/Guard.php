<?php

namespace SMSkin\IdentityServiceClient\Guards\Jwt;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\Macroable;
use SMSkin\IdentityServiceClient\Api\Requests\Auth\Email\Authorize as AuthorizeByEmail;
use SMSkin\IdentityServiceClient\Api\Requests\Auth\Email\Validate as ValidateByEmail;
use SMSkin\IdentityServiceClient\Api\Requests\Identity\GetIdentity;
use SMSkin\IdentityServiceClient\Guards\Jwt\Contracts\BaseGuard as GuardContract;
use SMSkin\IdentityServiceClient\Guards\Jwt\Exceptions\JWTException;
use SMSkin\IdentityServiceClient\Guards\Jwt\Exceptions\UserNotDefinedException;
use SMSkin\IdentityServiceClient\Models\Contracts\HasIdentity;
use SMSkin\IdentityServiceClient\Repository\UserRepository;
use function app;
use function config;

class Guard implements GuardContract
{
    use GuardHelpers, Macroable;

    /**
     * @var HasIdentity
     */
    protected $user;

    protected Request $request;

    protected Dispatcher $events;

    protected JWT $jwt;

    protected string $name;

    protected bool $loggedOut = false;

    public function __construct(JWT $jwt, UserProvider $provider, Request $request, Dispatcher $eventDispatcher, string $name)
    {
        $this->jwt = $jwt;
        $this->provider = $provider;
        $this->request = $request;
        $this->events = $eventDispatcher;
        $this->name = $name;
    }

    public function user(): ?HasIdentity
    {
        if ($this->loggedOut) {
            return null;
        }

        if (!is_null($this->user)) {
            return $this->user;
        }

        $token = $this->jwt->setRequest($this->request)->getToken();
        if (!$token) {
            $this->loggedOut = true;
            return null;
        }

        try {
            $identity = GetIdentity::execute($token);
        } catch (GuzzleException) {
            $this->loggedOut = true;
            return null;
        }

        $user = UserRepository::create($identity);
        $this->setUser($user);
        return $user;
    }

    /**
     * @return HasIdentity
     * @throws UserNotDefinedException
     */
    public function userOrFail(): HasIdentity
    {
        if (!$user = $this->user()) {
            throw new UserNotDefinedException();
        }

        return $user;
    }

    public function validate(array $credentials = []): bool
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'];

        if ($email) {
            try {
                return ValidateByEmail::execute($email, $password);
            } catch (GuzzleException) {
                return false;
            }
        }
        return false;
    }

    public function getToken(): ?JWT
    {
        return $this->jwt;
    }

    /**
     * @return void
     * @throws JWTException
     */
    public function logout(): void
    {
        $this->requireToken()->invalidate();

        $this->fireLogoutEvent($this->user);

        $this->user = null;
        $this->loggedOut = true;
        $this->jwt->unsetToken();
    }

    public function attempt(array $credentials = [], $remember = false): bool
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'];

        $this->fireAttemptEvent($credentials);

        if ($email) {
            return $this->attemptByEmail($email, $password);
        }

        return false;
    }

    /**
     * @return JWT
     * @throws JWTException
     */
    private function requireToken(): JWT
    {
        if (!$this->jwt->setRequest($this->request)->getToken()) {
            throw new JWTException('Token could not be parsed from the request.');
        }

        return $this->jwt;
    }

    private function attemptByEmail(string $email, string $password): bool
    {
        try {
            $jwt = AuthorizeByEmail::execute(
                $email, 
                $password,
                config('identity-service-client.scopes')
            );
            $identity = GetIdentity::execute($jwt->accessToken->value);
            $user = UserRepository::create($identity);
            $this->login($user, $jwt->accessToken->value);
        } catch (GuzzleException) {
            return false;
        }

        return true;
    }

    private function login(HasIdentity $user, string $token): void
    {
        $this->jwt->setToken($token);
        $this->setUser($user);
        $this->fireLoginEvent($user);
    }

    public function setUser(HasIdentity|Authenticatable $user)
    {
        $this->user = $user;
        $this->loggedOut = false;
        return $this;
    }

    protected function fireLogoutEvent($user)
    {
        $this->events->dispatch(new Logout(
            $this->name,
            $user
        ));
    }

    private function fireLoginEvent(HasIdentity $user, $remember = false)
    {
        $this->events->dispatch(new Login(
            $this->name,
            $user,
            $remember
        ));
    }

    private function fireAttemptEvent(array $credentials)
    {
        $this->events->dispatch(new Attempting(
            $this->name,
            $credentials,
            false
        ));
    }
}
