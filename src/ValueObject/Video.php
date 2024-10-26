<?php

namespace App\ValueObject;

class Video {
    private string $iso_639_1;
    private string $iso_3166_1;
    private string $name;
    private string $key;
    private string $site;
    private int $size;
    private string $type;
    private bool $official;
    private ?\DateTimeImmutable $published_at;
    private string $id;

    public function __construct(array $data) {
        $this->iso_639_1 = $data['iso_639_1'] ?? '';
        $this->iso_3166_1 = $data['iso_3166_1'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->key = $data['key'] ?? '';
        $this->site = $data['site'] ?? '';
        $this->size = $data['size'] ?? 0;
        $this->type = $data['type'] ?? '';
        $this->official = $data['official'] ?? false;
        $this->published_at = isset($data['published_at']) ? new \DateTimeImmutable($data['published_at']) : null;
        $this->id = $data['id'] ?? '';
    }

    // Getters
    public function getIso639_1(): string {
        return $this->iso_639_1;
    }

    public function getIso3166_1(): string {
        return $this->iso_3166_1;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function getSite(): string {
        return $this->site;
    }

    public function getSize(): int {
        return $this->size;
    }

    public function getType(): string {
        return $this->type;
    }

    public function isOfficial(): bool {
        return $this->official;
    }

    public function getPublishedAt(): DateTime {
        return $this->published_at;
    }

    public function getId(): string {
        return $this->id;
    }
}
