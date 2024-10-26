<?php

declare(strict_types=1);

namespace App\Repository;

use App\ValueObject\Video;

interface VideoRepositoryInterface
{

    public function getMovieVideoTrailer(int $id): ?Video;
}