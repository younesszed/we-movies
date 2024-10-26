<?php

declare(strict_types=1);

namespace App\Repository;

use App\Service\MovieApiClient;
use App\ValueObject\Genre;
use App\ValueObject\Video;
use http\Exception\RuntimeException;

class VideoApiMovieRepository implements VideoRepositoryInterface
{
    private MovieApiClient $apiClient;

    public function __construct(MovieApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getMovieVideoTrailer(int $movieId): ?Video
    {
        try {
            $data = $this->apiClient->get('/movie/'.$movieId.'/videos?');
            $videos = array_map(
                fn(array $videoData) => new Video($videoData),
                $data['results']
            );
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'Resource not found') {
                return null;
            }
            throw new RuntimeException('Failed to fetch movie: ' . $e->getMessage());
        }

        return current(array_filter($videos, fn (Video $video) => $video->getType() === 'Trailer'));
    }
}