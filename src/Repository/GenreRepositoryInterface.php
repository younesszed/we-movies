<?php

declare(strict_types=1);

namespace App\Repository;

use App\ValueObject\Genre;

interface GenreRepositoryInterface
{
    /**
     * @return Genre[]
     */
    public function findAll(): array;

    public function findById(int $id): ?Genre;
}