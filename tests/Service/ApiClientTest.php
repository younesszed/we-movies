<?php

namespace App\Tests\Service;

use App\Service\MovieApiClient;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiClientTest extends TestCase
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
     * @dataProvider requestMethodProvider
     */
    public function test_should_handle_http_methods(
        string $method,
        string $endpoint,
        array $params,
        array $expectedData
    ): void {
        $expectedOptions = [
            'headers' => [
                'Authorization' => 'Bearer ' . self::API_TOKEN,
                'Accept' => 'application/json'
            ]
        ];

        if (isset($params['query'])) {
            $expectedOptions['query'] = $params['query'];
        }
        if ($method === 'POST' && isset($params['json'])) {
            $expectedOptions['json'] = $params['json'];
        }

        $this->response
            ->method('getStatusCode')
            ->willReturn(200);

        $this->response
            ->method('getContent')
            ->willReturn(json_encode($expectedData));

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $method,
                self::BASE_URL . $endpoint,
                $expectedOptions
            )
            ->willReturn($this->response);

        $result = match($method) {
            'GET' => $this->movieApiClient->get($endpoint, $params['query'] ?? []),
            'POST' => $this->movieApiClient->post($endpoint, $params['json'] ?? [], $params['query'] ?? [])
        };

        $this->assertEquals($expectedData, $result);
    }

    /**
     * @return array<string, array{method: string, endpoint: string, params: array, expectedData: array}>
     */
    public static function requestMethodProvider(): array
    {
        return [
            'should handle GET request with query params' => [
                'method' => 'GET',
                'endpoint' => '/movie/popular',
                'params' => ['query' => ['page' => 1]],
                'expectedData' => [
                    'page' => 1,
                    'results' => [['id' => 1, 'title' => 'Movie 1']]
                ]
            ],
            'should handle POST request with body' => [
                'method' => 'POST',
                'endpoint' => '/list/1/items',
                'params' => [
                    'json' => ['media_id' => 1],
                    'query' => ['session_id' => 'abc123']
                ],
                'expectedData' => ['success' => true]
            ]
        ];
    }

    /**
     * @dataProvider httpStatusProvider
     */
    public function test_should_handle_http_status_codes(
        int $statusCode,
        ?string $expectedException,
        ?string $expectedMessage
    ): void {
        $this->response
            ->method('getStatusCode')
            ->willReturn($statusCode);

        $this->httpClient
            ->method('request')
            ->willReturn($this->response);

        if ($expectedException) {
            $this->expectException($expectedException);
            $this->expectExceptionMessage($expectedMessage);
        }

        $this->movieApiClient->get('/test');
    }

    /**
     * @return array<string, array{statusCode: int, expectedException: string|null, expectedMessage: string|null}>
     */
    public static function httpStatusProvider(): array
    {
        return [
            'should handle unauthorized' => [
                'statusCode' => 401,
                'expectedException' => RuntimeException::class,
                'expectedMessage' => 'Unauthorized access'
            ],
            'should handle forbidden' => [
                'statusCode' => 403,
                'expectedException' => RuntimeException::class,
                'expectedMessage' => 'Forbidden access'
            ],
            'should handle not found' => [
                'statusCode' => 404,
                'expectedException' => RuntimeException::class,
                'expectedMessage' => 'Resource not found'
            ],
            'should handle server error' => [
                'statusCode' => 500,
                'expectedException' => RuntimeException::class,
                'expectedMessage' => 'Unexpected response with status code: 500'
            ]
        ];
    }

    /**
     * @dataProvider networkErrorProvider
     */
    public function test_should_handle_network_errors(string $error): void
    {
        $this->httpClient
            ->method('request')
            ->willThrowException(new \Exception($error));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('API request failed: ' . $error);

        $this->movieApiClient->get('/test');
    }

    /**
     * @return array<string, array{error: string}>
     */
    public static function networkErrorProvider(): array
    {
        return [
            'should handle timeout' => [
                'error' => 'Connection timed out'
            ],
            'should handle DNS error' => [
                'error' => 'Could not resolve host'
            ],
            'should handle refused connection' => [
                'error' => 'Connection refused'
            ]
        ];
    }
}