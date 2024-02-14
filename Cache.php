<?php

namespace API;

class Cache
{
    private string $directory;

    public function __construct()
    {
        $this->directory = __DIR__.'/cache';
    }

    public function get(string $fichier)
    {
        $filename = "$this->directory/$fichier";

        if (is_file($filename) && filemtime($filename) >= strtotime('15 minutes ago')) {
            return unserialize(file_get_contents($filename));
        }

        return null;
    }

    public function set(string $fichier, $data) : void
    {
        file_put_contents("$this->directory/$fichier", serialize($data));
    }

    public function clear() : void
    {
        $files = glob("$this->directory/*");

        foreach($files as $file) {
            unlink($file);
        }
    }
}