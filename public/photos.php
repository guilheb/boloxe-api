<?php

use API\Cache;
use API\Cors;

require_once __DIR__ . '/../bootstrap.php';

Cors::handle();

header('Content-Type: application/json; charset=utf-8');

try {
    $cache = new Cache();
    print json_encode($cache->get('boloxe_portfolio_albums'), JSON_THROW_ON_ERROR);
}
catch (Exception) {
    http_response_code(503);
}
