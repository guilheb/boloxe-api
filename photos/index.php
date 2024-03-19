<?php

require_once '../config.php';
require_once '../Cache.php';
require_once 'Photos.php';
require_once 'FlickrAPI.php';
require_once 'Entities/Album.php';
require_once 'Entities/Photo.php';

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

$photos = new API\Photos\Photos();

header('Content-Type: application/json; charset=utf-8');

try {
    print json_encode($photos->fetch());
}
catch (Exception $e) {
    http_response_code(503);
}
