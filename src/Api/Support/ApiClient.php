<?php

namespace SMSkin\IdentityServiceClient\Api\Support;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use function app;

class ApiClient
{
    protected ?string $accessToken = null;

    /**
     * @param string $host
     * @param string $prefix
     * @throws Exception
     */
    public function __construct(protected string $host, protected string $prefix)
    {
        if (!$host) {
            throw new Exception('Api host not defined');
        }
        if (!$prefix)
        {
            throw new Exception('Api prefix not defined');
        }
    }

    /**
     * @param string $uri
     * @param array $queryParams
     * @param array $headers
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get(string $uri, array $queryParams = [], array $headers = []): ResponseInterface
    {
        $params = count($queryParams) ? '?' . http_build_query($queryParams) : '';

        return $this->getClient()->request(
            'GET',
            $this->host . '/' . $this->prefix . $uri . $params,
            [
                'headers' => array_merge(
                    $this->getDefaultHeaders(),
                    $headers
                )
            ]
        );
    }

    /**
     * @param string $uri
     * @param array $body
     * @param array $headers
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function post(string $uri, array $body = [], array $headers = []): ResponseInterface
    {
        return $this->getClient()->request(
            'POST',
            $this->host . $uri,
            [
                RequestOptions::JSON => $body,
                'headers' => array_merge(
                    $this->getDefaultHeaders(),
                    $headers
                )
            ]
        );
    }

    protected function getDefaultHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
        if ($this->accessToken) {
            $headers['Authorization'] = 'Bearer ' . $this->accessToken;
        }
        return $headers;
    }

    private function getClient(): Client
    {
        return app(Client::class);
    }

    /**
     * @param string|null $accessToken
     * @return ApiClient
     */
    public function setAccessToken(?string $accessToken): ApiClient
    {
        $this->accessToken = $accessToken;
        return $this;
    }
}
