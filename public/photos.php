<?php

use API\Cache;

require_once __DIR__ . '/../bootstrap.php';

$origin = $_SERVER['HTTP_ORIGIN'] ?? null;

if ($origin !== null) {
    // Allow localhost or boloxe.com
    if (!preg_match('/^https?:\/\/(([\w-]+\.)*localhost|(www\.)?boloxe\.com)(:\d+)?$/D', $origin)) {
        http_response_code(403);
        exit;
    }

    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
}

header('Content-Type: application/json; charset=utf-8');

try {
    $cache = new Cache();
    print json_encode($cache->get('boloxe_portfolio_albums'), JSON_THROW_ON_ERROR);
}
catch (Exception) {
    http_response_code(503);
}
