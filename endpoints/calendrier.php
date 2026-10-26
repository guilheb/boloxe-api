<?php

use API\Calendars\Calendars;
use API\Cors;

// Before the key check: the preflight request doesn't carry the key
Cors::handle(['X-Internal-Api-Key']);

// Refused as long as INTERNAL_API_KEY isn't set
$expectedKey = $_SERVER['INTERNAL_API_KEY'] ?? '';
$givenKey = $_SERVER['HTTP_X_INTERNAL_API_KEY'] ?? '';

if ($expectedKey === '' || !hash_equals($expectedKey, $givenKey)) {
    http_response_code(401);
    exit;
}

header('Cache-Control: no-store');

$calendars = new Calendars();
$id = $_GET['id'] ?? null;

// Without an id: the calendar list (id, name, color, never the URLs)
if ($id === null) {
    header('Content-Type: application/json; charset=utf-8');
    print json_encode($calendars->all,JSON_THROW_ON_ERROR);
    exit;
}

$calendar = is_string($id) ? $calendars->get($id) : null;

if ($calendar === null) {
    http_response_code(404);
    exit;
}

try {
    $ics = $calendars->fetch($calendar);
}
catch (Exception $e) {
    error_log("[calendrier] {$calendar->id}: {$e->getMessage()}");
    http_response_code(502);
    exit;
}

header('Content-Type: text/calendar; charset=utf-8');
print $ics;
