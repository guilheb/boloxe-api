<?php

namespace API;

final class Cors
{
    // boloxe.com and its subdomains, plus localhost for development
    private const string ALLOWED_ORIGINS = '/^https?:\/\/(([\w-]+\.)*localhost|([\w-]+\.)*boloxe\.com)(:\d+)?$/D';

    private const int PREFLIGHT_MAX_AGE = 86400;

    /**
     * Sends the CORS headers for an allowed Origin and refuses the others with a 403.
     * A preflight request (OPTIONS) is answered right away.
     *
     * @param string[] $allowedHeaders Request headers the browser may send, besides the simple ones
     */
    public static function handle(array $allowedHeaders = []): void
    {
        header('Vary: Origin');

        $origin = $_SERVER['HTTP_ORIGIN'] ?? null;

        if ($origin === null) {
            return;
        }

        if (!preg_match(self::ALLOWED_ORIGINS, $origin)) {
            http_response_code(403);
            exit;
        }

        header("Access-Control-Allow-Origin: $origin");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Methods: GET');
            if ($allowedHeaders) {
                header('Access-Control-Allow-Headers: ' . implode(', ', $allowedHeaders));
            }
            header('Access-Control-Max-Age: ' . self::PREFLIGHT_MAX_AGE);
            http_response_code(204);
            exit;
        }
    }
}
