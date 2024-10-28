<?php

namespace App\Tests\Repository;

use App\Repository\GenreApiMovieRepository;
use App\Service\MovieApiClient;
use App\ValueObject\Genre;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class GenreApiMovieRepositoryTest extends TestCase
{
    private GenreApiMovieRepository $genreRepository;
    private MovieApiClient $apiClient;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(MovieApiClient::class);
        $this->genreRepository = new GenreApiMovieRepository($this->apiClient);
    }

    /**
     * @dataProvider genreOperationsProvider
     */
    public function test_genre_operations(
        string $method,
        array $params,
        array $mockData,
        ?string $expectedException
    ): void {
        if ($expectedException) {
            $this->apiClient
                ->method('get')
                ->willThrowException(new RuntimeException($mockData['error']));

            $this->expectException($expectedException);
            $this->expectExceptionMessage($mockData['errorMessage']);
        } else {
            $this->apiClient
                ->method('get')
                ->willReturn(['genres' => $mockData['genres']]);
        }

        $result = match($method) {
            'findAll' => $this->genreRepository->findAll(),
            'findById' => $this->genreRepository->findById($params['id']),
            'findByIds' => $this->genreRepository->findByIds($params['ids'])
        };

        if (!$expectedException) {
            if ($method === 'findById') {
                $this->assertInstanceOf(Genre::class, $result);
                $this->assertEquals($mockData['expectedResult']['id'], $result->getId());
            } else {
                $this->assertCount(count($mockData['expectedResult']), $result);
                $this->assertContainsOnlyInstancesOf(Genre::class, $result);
            }
        }
    }

    /**
     * @return array<string, array{
     *    method: string,
     *    params: array,
     *    mockData: array,
     *    expectedException: string|null
     * }>
     */
    public static function genreOperationsProvider(): array
    {
        $genresData = [
            ['id' => 28, 'name' => 'Action'],
            ['id' => 12, 'name' => 'Adventure'],
            ['id' => 16, 'name' => 'Animation']
        ];

        return [
            'find all genres' => [
                'method' => 'findAll',
                'params' => [],
                'mockData' => [
                    'genres' => $genresData,
                    'expectedResult' => $genresData
                ],
                'expectedException' => null
            ],
            'find genre by id' => [
                'method' => 'findById',
                'params' => ['id' => 28],
                'mockData' => [
                    'genres' => $genresData,
                    'expectedResult' => ['id' => 28, 'name' => 'Action']
                ],
                'expectedException' => null
            ],
            'find genres by ids' => [
                'method' => 'findByIds',
                'params' => ['ids' => [28, 12]],
                'mockData' => [
                    'genres' => $genresData,
                    'expectedResult' => [
                        ['id' => 28, 'name' => 'Action'],
                        ['id' => 12, 'name' => 'Adventure']
                    ]
                ],
                'expectedException' => null
            ],
            'handle api error' => [
                'method' => 'findAll',
                'params' => [],
                'mockData' => [
                    'error' => 'API Error',
                    'errorMessage' => 'Failed to load genres: API Error'
                ],
                'expectedException' => RuntimeException::class
            ]
        ];
    }
}