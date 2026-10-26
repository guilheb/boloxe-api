<?php

namespace API\Calendars\Entities;

use JsonSerializable;

final readonly class Calendar implements JsonSerializable
{
    public function __construct(
        public string $id,
        public ?string $name,
        public string $color,
        public string $url,
    ) {}

    // The feed URL is left out: it must not leave the server
    public function jsonSerialize(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'color' => $this->color];
    }
}
