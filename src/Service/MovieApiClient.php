<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MovieApiClient
{
    private HttpClientInterface $client;
    private string $token;
    private string $baseUrl;

    public function __construct(
        HttpClientInterface $client,
        string $token,
        string $baseUrl
    ) {
        $this->client = $client;
        $this->token = $token;
        $this->baseUrl = $baseUrl;
    }

    public function get(string $endpoint, array $queryParams = []): array
    {
        return $this->request('GET', $endpoint, queryParams: $queryParams);
    }

    public function post(string $endpoint, array $data = [], array $queryParams = []): array
    {
        return $this->request('POST', $endpoint, $data, $queryParams);
    }

    private function request(
        string $method,
        string $endpoint,
        array $data = [],
        array $queryParams = []
    ): array {
        try {
            $response = $this->client->request(
                $method,
                $this->baseUrl . $endpoint,
                array_filter([
                    'headers' => [
                        'Authorization' => "Bearer {$this->token}",
                        'Accept' => 'application/json',
                    ],
                    'query' => $queryParams,
                    'json' => $data,
                ])
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {

            throw new \RuntimeException(
                "API request failed: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    private function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        if ($statusCode === 204) {
            return [];
        }

        if ($statusCode >= 200 && $statusCode < 300) {
            $result = json_decode($content, true);
            return $result;
        }

        throw match($statusCode) {
            404 => new \RuntimeException('Resource not found'),
            401 => new \RuntimeException('Unauthorized access'),
            403 => new \RuntimeException('Forbidden access'),
            default => new \RuntimeException("Unexpected response with status code: $statusCode")
        };
    }
}