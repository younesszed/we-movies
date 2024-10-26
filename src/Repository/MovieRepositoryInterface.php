<?php

declare(strict_types=1);

namespace App\Repository;

use App\ValueObject\Movie;

interface MovieRepositoryInterface
{
    /**
     * @return Movie[]
     */
    public function findAll(int $page = 1): array;

    public function findById(int $id): ?Movie;

    /**
     * @return Movie[]
     */
    public function findByTitle(string $title, int $page = 1): array;

    /**
     * @return Movie[]
     */
    public function findPopular(int $page = 1): array;

    /**
     * @param int[] $genreIds
     * @return Movie[]
     */
    public function findByGenres(array $genreIds, int $page = 1): array;

    /**
     * @return Movie[]
     */
    public function findUpcoming(int $page = 1): array;

    /**
     * @return Movie[]
     */
    public function findTopRated(int $page = 1): array;

    /**
     * @return Movie[]
     */
    public function findSimilar(int $movieId, int $page = 1): array;
}