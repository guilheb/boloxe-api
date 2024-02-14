<?php

namespace API\Photos\Entities;

class Photo
{
    public string $url;
    public ?string $title;
    public int $height;
    public int $width;

    public function __construct(string $url, ?string $title, int $height, int $width)
    {
        $this->url = $url;
        $this->title = $title;
        $this->height = $height;
        $this->width = $width;
    }
}