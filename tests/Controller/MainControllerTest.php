<?php

namespace App\Tests\Controller;

use App\Controller\MainController;
use App\Repository\GenreRepositoryInterface;
use App\Repository\MovieRepositoryInterface;
use App\ValueObject\Genre;
use App\ValueObject\Movie;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class MainControllerTest extends KernelTestCase
{
    private MainController $controller;
    private GenreRepositoryInterface $genreRepository;
    private MovieRepositoryInterface $movieRepository;
    private Environment $twig;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->genreRepository = $this->createMock(GenreRepositoryInterface::class);
        $this->movieRepository = $this->createMock(MovieRepositoryInterface::class);
        $this->twig = $this->createMock(Environment::class);

        $this->twig
            ->method('render')
            ->willReturn('<html>Mocked template content</html>');

        $container = static::getContainer();

        $this->controller = new MainController(
            $this->genreRepository,
            $this->movieRepository
        );

        $this->controller->setContainer($container);
        $container->set('twig', $this->twig);
    }

    private const SAMPLE_MOVIE_DATA = [
        'id' => 1,
        'title' => 'The Test Movie',
        'original_title' => 'Le Film Test',
        'overview' => 'A comprehensive movie description for testing purposes that covers multiple aspects of the story.',
        'poster_path' => '/path/to/poster.jpg',
        'backdrop_path' => '/path/to/backdrop.jpg',
        'release_date' => '2024-01-15',
        'status' => 'Released',
        'tagline' => 'Testing has never been more exciting',
        'runtime' => 120,
        'budget' => 150000000,
        'revenue' => 350000000,
        'vote_average' => 8.5,
        'vote_count' => 2500,
        'popularity' => 125.5,
        'original_language' => 'en',
        'spoken_languages' => [
            ['iso_639_1' => 'en', 'name' => 'English'],
            ['iso_639_1' => 'fr', 'name' => 'French']
        ],
        'genre_ids' => [28, 12, 878],
        'production_companies' => [
            [
                'id' => 1,
                'name' => 'Test Studios',
                'logo_path' => '/path/to/studio/logo.png',
                'origin_country' => 'US'
            ],
            [
                'id' => 2,
                'name' => 'Mock Productions',
                'logo_path' => '/path/to/production/logo.png',
                'origin_country' => 'GB'
            ]
        ],
        'adult' => false,
        'video' => true,
        'trailer' => [
            'id' => 'tr1',
            'key' => 'xyz789',
            'name' => 'Official Trailer',
            'site' => 'YouTube',
            'size' => 1080,
            'type' => 'Trailer',
            'official' => true,
            'published_at' => '2023-12-01T10:00:00.000Z',
            'iso_639_1' => 'en'
        ],
        'credits' => [
            'cast' => [
                [
                    'id' => 101,
                    'name' => 'John Actor',
                    'character' => 'Main Character',
                    'profile_path' => '/path/to/actor1.jpg',
                    'order' => 1
                ],
                [
                    'id' => 102,
                    'name' => 'Jane Actress',
                    'character' => 'Supporting Role',
                    'profile_path' => '/path/to/actor2.jpg',
                    'order' => 2
                ]
            ],
            'crew' => [
                [
                    'id' => 201,
                    'name' => 'Director Name',
                    'job' => 'Director',
                    'department' => 'Directing',
                    'profile_path' => '/path/to/director.jpg'
                ]
            ]
        ],
        'images' => [
            'backdrops' => [
                [
                    'file_path' => '/path/to/backdrop1.jpg',
                    'width' => 1920,
                    'height' => 1080,
                    'iso_639_1' => 'en'
                ]
            ],
            'posters' => [
                [
                    'file_path' => '/path/to/poster1.jpg',
                    'width' => 2000,
                    'height' => 3000,
                    'iso_639_1' => 'en'
                ]
            ]
        ]
    ];

    /**
     * @dataProvider pageProvider
     */
    public function test_should_render_pages(
        string $method,
        array $input,
        array $mockData,
        int $expectedStatus
    ): void {
        $this->setupMocks($method, $input, $mockData);

        $response = match($method) {
            'index' => $this->controller->index(),
            'search' => $this->controller->search(new Request(['query' => $input['query']])),
            'moviesByGenres' => $this->controller->moviesByGenres(new Request([], ['genres' => $input['genres']])),
            'movieModal' => $this->controller->movieModal($input['id']),
        };

        $this->assertEquals($expectedStatus, $response->getStatusCode());
        $this->assertJsonResponseIsValid($response->getContent(), $method);
    }

    /**
     * @return array<string, array{method: string, input: array, mockData: array, expectedStatus: int}>
     */
    public static function pageProvider(): array
    {
        $testMovie = new Movie(self::SAMPLE_MOVIE_DATA);

        $genres = [
            new Genre(['id' => 28, 'name' => 'Action']),
            new Genre(['id' => 12, 'name' => 'Adventure']),
            new Genre(['id' => 878, 'name' => 'Science Fiction'])
        ];

        return [
            'should render index page' => [
                'method' => 'index',
                'input' => [],
                'mockData' => [
                    'genres' => $genres,
                    'movies' => [$testMovie]
                ],
                'expectedStatus' => 200
            ],
            'should search movies' => [
                'method' => 'search',
                'input' => ['query' => 'test'],
                'mockData' => [
                    'movies' => [$testMovie]
                ],
                'expectedStatus' => 200
            ],
            'should filter movies by genres' => [
                'method' => 'moviesByGenres',
                'input' => ['genres' => [28, 12]],
                'mockData' => [
                    'movies' => [$testMovie]
                ],
                'expectedStatus' => 200
            ],
            'should show movie details modal' => [
                'method' => 'movieModal',
                'input' => ['id' => 1],
                'mockData' => [
                    'movie' => $testMovie
                ],
                'expectedStatus' => 200
            ],
            'should handle movie not found' => [
                'method' => 'movieModal',
                'input' => ['id' => 999],
                'mockData' => [
                    'movie' => null
                ],
                'expectedStatus' => 404
            ],
            'should handle empty search results' => [
                'method' => 'search',
                'input' => ['query' => 'nonexistent'],
                'mockData' => [
                    'movies' => []
                ],
                'expectedStatus' => 200
            ],
            'should handle empty genre results' => [
                'method' => 'moviesByGenres',
                'input' => ['genres' => [999]],
                'mockData' => [
                    'movies' => []
                ],
                'expectedStatus' => 200
            ]
        ];
    }

    private function setupMocks(string $method, array $input, array $mockData): void
    {
        match($method) {
            'index' => $this->setupIndexMocks($mockData),
            'search' => $this->setupSearchMocks($input, $mockData),
            'moviesByGenres' => $this->setupGenresMocks($input, $mockData),
            'movieModal' => $this->setupModalMocks($input, $mockData),
        };
    }

    private function setupIndexMocks(array $mockData): void
    {
        $this->genreRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($mockData['genres']);

        $this->movieRepository
            ->expects($this->once())
            ->method('findPopular')
            ->willReturn($mockData['movies']);
    }

    private function setupSearchMocks(array $input, array $mockData): void
    {
        $this->movieRepository
            ->expects($this->once())
            ->method('findByTitle')
            ->with($input['query'])
            ->willReturn($mockData['movies']);
    }

    private function setupGenresMocks(array $input, array $mockData): void
    {
        $this->movieRepository
            ->expects($this->once())
            ->method('findByGenres')
            ->with($input['genres'])
            ->willReturn($mockData['movies']);
    }

    private function setupModalMocks(array $input, array $mockData): void
    {
        $this->movieRepository
            ->expects($this->once())
            ->method('findById')
            ->with($input['id'])
            ->willReturn($mockData['movie']);
    }

    private function assertJsonResponseIsValid(?string $content, string $method): void
    {
        if (!$content || $method === 'index') {
            return;
        }

        $data = json_decode($content, true);

        match($method) {
            'search', 'moviesByGenres' => $this->assertArrayHasKey('html', $data),
            'movieModal' => $this->assertThat(
                $data,
                $this->logicalOr(
                    $this->arrayHasKey('html'),
                    $this->arrayHasKey('error')
                )
            )
        };
    }
}