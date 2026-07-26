<?php

use API\Cache;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Allow localhost or boloxe.com
    $regex = '/^http(s)?:\/\/((.*\.)?localhost|(www.)?boloxe.com)(:\d+)?/';

    if (preg_match($regex, $_SERVER['HTTP_ORIGIN'] ?? null, $matches)) {
        header("Access-Control-Allow-Origin: {$matches[0]}");
        header("Vary: Origin");
    } else {
        http_response_code(403);
        exit;
    }
}

header('Content-Type: application/json; charset=utf-8');

try {
    $cache = new Cache();
    print json_encode($cache->get('boloxe_portfolio_albums'));
}
catch (Exception $e) {
    http_response_code(503);
}
