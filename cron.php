<?php

use API\Cache;
use API\Photos\Photos;

require_once __DIR__ . '/vendor/autoload.php';

$photos = new Photos();

$cache = new Cache();
$cache->set('boloxe_portfolio_albums', $photos->fetch());
