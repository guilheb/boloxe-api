<?php

use API\Cache;
use API\Photos\Photos;
use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$photos = new Photos();

$cache = new Cache();
$cache->set('boloxe_portfolio_albums', $photos->fetch());
