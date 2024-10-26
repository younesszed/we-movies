<?php

declare(strict_types=1);

namespace App\Repository;

use App\Service\MovieApiClient;
use App\ValueObject\Genre;

class GenreApiMovieRepository implements GenreRepositoryInterface
{
    private MovieApiClient $apiClient;
    private ?array $genres = null;

    public function __construct(MovieApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @return Genre[]
     */
    public function findAll(): array
    {
        if ($this->genres === null) {
            $this->loadGenres();
        }

        return $this->genres;
    }

    public function findById(int $id): ?Genre
    {
        if ($this->genres === null) {
            $this->loadGenres();
        }

        foreach ($this->genres as $genre) {
            if ($genre->getId() === $id) {
                return $genre;
            }
        }

        return null;
    }

    /**
     * @param int[] $ids
     * @return Genre[]
     */
    public function findByIds(array $ids): array
    {
        if ($this->genres === null) {
            $this->loadGenres();
        }

        return array_filter(
            $this->genres,
            fn(Genre $genre) => in_array($genre->getId(), $ids)
        );
    }

    private function loadGenres(): void
    {
        try {
            $data = $this->apiClient->get('/genre/movie/list');
            $this->genres = array_map(
                fn(array $genreData) => new Genre($genreData),
                $data['genres']
            );
        } catch (\Exception $e) {
            $this->genres = [];
            throw new \RuntimeException('Failed to load genres: ' . $e->getMessage());
        }
    }
}