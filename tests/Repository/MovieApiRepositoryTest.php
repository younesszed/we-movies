<?php

namespace App\Tests\Service;

use App\Service\MovieApiClient;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MovieApiClientTest extends TestCase
{
    private const BASE_URL = 'https://api.themoviedb.org/3';
    private const API_TOKEN = 'fake-test-token';

    private MovieApiClient $movieApiClient;
    private HttpClientInterface $httpClient;
    private ResponseInterface $response;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->movieApiClient = new MovieApiClient(
            $this->httpClient,
            self::API_TOKEN,
            self::BASE_URL
        );
    }

    /**
     * @dataProvider apiRequestProvider
     */
    public function test_api_requests(
        string $method,
        array $requestData,
        array $mockConfig,
        ?string $expectedException
    ): void {
        $this->setupMockResponse($mockConfig);

        if ($expectedException) {
            $this->expectException($expectedException);
            if (isset($mockConfig['errorMessage'])) {
                $this->expectExceptionMessage($mockConfig['errorMessage']);
            }
        }

        $result = match($method) {
            'GET' => $this->movieApiClient->get(
                $requestData['endpoint'],
                $requestData['query'] ?? []
            ),
            'POST' => $this->movieApiClient->post(
                $requestData['endpoint'],
                $requestData['body'] ?? [],
                $requestData['query'] ?? []
            )
        };

        if (!$expectedException) {
            $this->assertEquals($mockConfig['responseData'] ?? [], $result);
        }
    }

    /**
     * @return array<string, array{
     *    method: string,
     *    requestData: array,
     *    mockConfig: array,
     *    expectedException: string|null
     * }>
     */
    public static function apiRequestProvider(): array
    {
        return [
            'successful GET request' => [
                'method' => 'GET',
                'requestData' => [
                    'endpoint' => '/movie/popular',
                    'query' => ['page' => 1]
                ],
                'mockConfig' => [
                    'statusCode' => 200,
                    'responseData' => [
                        'results' => [
                            ['id' => 1, 'title' => 'Movie 1']
                        ]
                    ]
                ],
                'expectedException' => null
            ],
            'successful POST request' => [
                'method' => 'POST',
                'requestData' => [
                    'endpoint' => '/list/1/items',
                    'body' => ['media_id' => 1],
                    'query' => ['session_id' => 'abc']
                ],
                'mockConfig' => [
                    'statusCode' => 200,
                    'responseData' => ['success' => true]
                ],
                'expectedException' => null
            ],
            'unauthorized error' => [
                'method' => 'GET',
                'requestData' => ['endpoint' => '/movie/1'],
                'mockConfig' => [
                    'statusCode' => 401,
                    'errorMessage' => 'Unauthorized access'
                ],
                'expectedException' => RuntimeException::class
            ],
            'not found error' => [
                'method' => 'GET',
                'requestData' => ['endpoint' => '/movie/999'],
                'mockConfig' => [
                    'statusCode' => 404,
                    'errorMessage' => 'Resource not found'
                ],
                'expectedException' => RuntimeException::class
            ],
            'network error' => [
                'method' => 'GET',
                'requestData' => ['endpoint' => '/test'],
                'mockConfig' => [
                    'exception' => new \Exception('Connection failed'),
                    'errorMessage' => 'API request failed: Connection failed'
                ],
                'expectedException' => RuntimeException::class
            ],
        ];
    }

    private function setupMockResponse(array $mockConfig): void
    {
        if (isset($mockConfig['exception'])) {
            $this->httpClient
                ->method('request')
                ->willThrowException($mockConfig['exception']);
            return;
        }

        $this->response
            ->method('getStatusCode')
            ->willReturn($mockConfig['statusCode']);

        if (isset($mockConfig['responseData'])) {
            $this->response
                ->method('getContent')
                ->willReturn(json_encode($mockConfig['responseData']));
        }

        $this->httpClient
            ->method('request')
            ->willReturn($this->response);
    }

    private function getExpectedRequestOptions(array $params): array
    {
        return array_filter([
            'headers' => [
                'Authorization' => 'Bearer ' . self::API_TOKEN,
                'Accept' => 'application/json'
            ],
            'query' => $params['query'] ?? null,
            'json' => $params['body'] ?? null
        ]);
    }
}