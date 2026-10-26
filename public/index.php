<?php

use Uri\Rfc3986\Uri;

// Every request goes through here (nginx: try_files … /index.php).
// Locally: php -S localhost:8000 -t public public/index.php
require_once __DIR__ . '/../bootstrap.php';

$path = rtrim(Uri::parse($_SERVER['REQUEST_URI'])?->getPath() ?? '', '/');

match ($path) {
    '/photos'     => require __DIR__ . '/../endpoints/photos.php',
    '/calendrier' => require __DIR__ . '/../endpoints/calendrier.php',
    default       => http_response_code(404),
};
