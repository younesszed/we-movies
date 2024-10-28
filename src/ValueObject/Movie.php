<?php

namespace App\ValueObject;

class Movie implements ValueObjectInterface
{
    private bool $adult;
    private ?string $backdropPath;
    private array $genreIds;
    private int $id;
    private string $originalLanguage;
    private string $originalTitle;
    private string $overview;
    private float $popularity;
    private ?string $posterPath;
    private ?\DateTimeImmutable $releaseDate;
    private string $title;
    private bool $video;
    private float $voteAverage;
    private int $voteCount;

    private ?Video $trailer;

    public function __construct(array $data)
    {
        $this->adult = $data['adult'];
        $this->backdropPath = $data['backdrop_path'];
        $this->genreIds = $data['genre_ids'] ?? $data['genres'];
        $this->id = $data['id'];
        $this->originalLanguage = $data['original_language'];
        $this->originalTitle = $data['original_title'];
        $this->overview = $data['overview'];
        $this->popularity = $data['popularity'];
        $this->posterPath = $data['poster_path'];
        $this->releaseDate = $data['release_date'] ? new \DateTimeImmutable($data['release_date']) : null;
        $this->title = $data['title'];
        $this->video = $data['video'];
        $this->voteAverage = $data['vote_average'];
        $this->voteCount = $data['vote_count'];
        $this->trailer = !empty($data['trailer']) ? new Video($data['trailer']) : null;
    }

    public function isAdult(): bool
    {
        return $this->adult;
    }

    public function getBackdropPath(): ?string
    {
        return $this->backdropPath;
    }

    public function getGenreIds(): array
    {
        return $this->genreIds;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOriginalLanguage(): string
    {
        return $this->originalLanguage;
    }

    public function getOriginalTitle(): string
    {
        return $this->originalTitle;
    }

    public function getOverview(): string
    {
        return $this->overview;
    }

    public function getPopularity(): float
    {
        return $this->popularity;
    }

    public function getPosterPath(): ?string
    {
        return $this->posterPath;
    }

    public function getReleaseDate(): ?\DateTimeImmutable
    {
        return $this->releaseDate;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isVideo(): bool
    {
        return $this->video;
    }

    public function getVoteAverage(): float
    {
        return $this->voteAverage;
    }

    public function getVoteCount(): int
    {
        return $this->voteCount;
    }

    public function getTrailer(): ?Video
    {
        return $this->trailer;
    }
}