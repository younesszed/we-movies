<?php

namespace App\Tests\DataProvider;

/**
 * Shared test data providers for Movie and Genre objects
 */
class TestDataProvider
{
    public static function getCompleteMovieData(): array
    {
        return [
            'id' => 1,
            'title' => 'Test Movie',
            'original_title' => 'Original Test Movie',
            'overview' => 'A comprehensive test movie description that covers all the details needed for testing.',
            'release_date' => '2024-01-01',
            'poster_path' => '/path/to/poster.jpg',
            'backdrop_path' => '/path/to/backdrop.jpg',
            'vote_average' => 7.5,
            'vote_count' => 1500,
            'popularity' => 123.45,
            'original_language' => 'en',
            'adult' => false,
            'genre_ids' => [28, 12, 878],
            'video' => false,
            'status' => 'Released',
            'runtime' => 120,
            'budget' => 150000000,
            'revenue' => 500000000,
            'tagline' => 'An epic test movie',
            'production_companies' => [
                [
                    'id' => 1,
                    'name' => 'Test Studio',
                    'logo_path' => '/path/to/logo.png',
                    'origin_country' => 'US'
                ]
            ],
            'spoken_languages' => [
                [
                    'iso_639_1' => 'en',
                    'name' => 'English'
                ],
                [
                    'iso_639_1' => 'fr',
                    'name' => 'French'
                ]
            ]
        ];
    }

    public static function getMovieWithVideosData(): array
    {
        return array_merge(self::getCompleteMovieData(), [
            'videos' => [
                'results' => [
                    [
                        'id' => 'video1',
                        'key' => 'trailer123',
                        'name' => 'Official Trailer',
                        'site' => 'YouTube',
                        'size' => 1080,
                        'type' => 'Trailer',
                        'official' => true,
                        'published_at' => '2024-01-01T00:00:00.000Z',
                        'iso_639_1' => 'en'
                    ],
                    [
                        'id' => 'video2',
                        'key' => 'teaser456',
                        'name' => 'Teaser',
                        'site' => 'YouTube',
                        'size' => 1080,
                        'type' => 'Teaser',
                        'official' => true,
                        'published_at' => '2023-12-01T00:00:00.000Z',
                        'iso_639_1' => 'en'
                    ]
                ]
            ]
        ]);
    }

    public static function getCompleteGenreData(): array
    {
        return [
            [
                'id' => 28,
                'name' => 'Action',
            ],
            [
                'id' => 12,
                'name' => 'Adventure',
            ],
            [
                'id' => 16,
                'name' => 'Animation',
            ],
            [
                'id' => 35,
                'name' => 'Comedy',
            ],
            [
                'id' => 80,
                'name' => 'Crime',
            ],
            [
                'id' => 99,
                'name' => 'Documentary',
            ],
            [
                'id' => 18,
                'name' => 'Drama',
            ],
            [
                'id' => 10751,
                'name' => 'Family',
            ],
            [
                'id' => 14,
                'name' => 'Fantasy',
            ],
            [
                'id' => 36,
                'name' => 'History',
            ],
            [
                'id' => 27,
                'name' => 'Horror',
            ],
            [
                'id' => 10402,
                'name' => 'Music',
            ],
            [
                'id' => 9648,
                'name' => 'Mystery',
            ],
            [
                'id' => 10749,
                'name' => 'Romance',
            ],
            [
                'id' => 878,
                'name' => 'Science Fiction',
            ],
            [
                'id' => 10770,
                'name' => 'TV Movie',
            ],
            [
                'id' => 53,
                'name' => 'Thriller',
            ],
            [
                'id' => 10752,
                'name' => 'War',
            ],
            [
                'id' => 37,
                'name' => 'Western',
            ]
        ];
    }
}
