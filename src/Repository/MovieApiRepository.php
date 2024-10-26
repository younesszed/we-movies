<?php

declare(strict_types=1);

namespace App\Repository;

use App\Service\MovieApiClient;
use App\ValueObject\Movie;
use RuntimeException;

class MovieApiRepository implements MovieRepositoryInterface
{
    private MovieApiClient $apiClient;

    private VideoRepositoryInterface $videoRepository;

    /**
     * @param MovieApiClient $apiClient
     * @param VideoRepositoryInterface $videoRepository
     */
    public function __construct(
        MovieApiClient $apiClient,
        VideoRepositoryInterface $videoRepository
    ) {
        $this->apiClient = $apiClient;
        $this->videoRepository = $videoRepository;
    }


    public function findAll(int $page = 1): array
    {
        try {
            $data = $this->apiClient->get('/discover/movie', [
                'page' => $page,
                'sort_by' => 'popularity.desc',
            ]);

            return $this->hydrateMovies($data['results']);
        } catch (RuntimeException $e) {
            throw new RuntimeException('Failed to fetch movies: '.$e->getMessage());
        }
    }

    public function findById(int $id): ?Movie
    {
        try {
            $data = $this->apiClient->get("/movie/$id");
            $trailer = $this->videoRepository->getMovieVideoTrailer($id);
            $data['trailer'] = $trailer;
            return new Movie($data);
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'Resource not found') {
                return null;
            }
            throw new RuntimeException('Failed to fetch movie: '.$e->getMessage());
        }
    }

    public function findByTitle(string $title, int $page = 1): array
    {
        try {
            $data = $this->apiClient->get('/search/movie', [
                'query' => $title,
                'page' => $page,
            ]);

            return $this->hydrateMovies($data['results']);
        } catch (RuntimeException $e) {
            throw new RuntimeException('Failed to search movies: '.$e->getMessage());
        }
    }

    public function findPopular(int $page = 1): array
    {
        try {
            $data = $this->apiClient->get('/movie/popular', [
                'page' => $page,
            ]);

            return $this->hydrateMovies($data['results']);
        } catch (RuntimeException $e) {
            throw new RuntimeException('Failed to fetch popular movies: '.$e->getMessage());
        }
    }

    public function findByGenres(array $genreIds, int $page = 1): array
    {
        try {
            $data = $this->apiClient->get('/discover/movie', [
                'with_genres' => implode(',', $genreIds),
                'page' => $page,
                'sort_by' => 'popularity.desc',
            ]);

            return $this->hydrateMovies($data['results']);
        } catch (RuntimeException $e) {
            throw new RuntimeException('Failed to fetch movies by genres: '.$e->getMessage());
        }
    }

    public function findUpcoming(int $page = 1): array
    {
        try {
            $data = $this->apiClient->get('/movie/upcoming', [
                'page' => $page,
            ]);

            return $this->hydrateMovies($data['results']);
        } catch (RuntimeException $e) {
            throw new RuntimeException('Failed to fetch upcoming movies: '.$e->getMessage());
        }
    }

    public function findTopRated(int $page = 1): array
    {
        try {
            $data = $this->apiClient->get('/movie/top_rated', [
                'page' => $page,
            ]);

            return $this->hydrateMovies($data['results']);
        } catch (RuntimeException $e) {
            throw new RuntimeException('Failed to fetch top rated movies: '.$e->getMessage());
        }
    }

    public function findSimilar(int $movieId, int $page = 1): array
    {
        try {
            $data = $this->apiClient->get("/movie/$movieId/similar", [
                'page' => $page,
            ]);

            return $this->hydrateMovies($data['results']);
        } catch (RuntimeException $e) {
            throw new RuntimeException('Failed to fetch similar movies: '.$e->getMessage());
        }
    }

    private function hydrateMovies(array $moviesData): array
    {
        return array_map(
            fn(array $movieData): Movie => new Movie($movieData),
            $moviesData
        );
    }
}