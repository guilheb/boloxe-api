<?php

namespace API\Photos\Entities;

class Album
{
    public string $id;
    public string $title;
    public ?string $description;
    public array $photos = [];

    public function __construct(string $id, string $title, ?string $description)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
    }

    public function addPhoto(Photo $photo): void
    {
        $this->photos[] = $photo;
    }
}