<?php

namespace API\Photos\Entities;

final readonly class Photo
{
    public function __construct(
        public string $url,
        public ?string $title,
        public int $height,
        public int $width,
    ) {}
}
