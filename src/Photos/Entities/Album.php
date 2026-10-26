<?php

namespace API\Photos\Entities;

final class Album
{
    /** @var Photo[] */
    private(set) array $photos = [];

    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly ?string $description,
    ) {}

    public function addPhoto(Photo $photo): void
    {
        $this->photos[] = $photo;
    }
}
