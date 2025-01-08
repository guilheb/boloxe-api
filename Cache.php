<?php

namespace API;

use Redis;

class Cache
{
    private Redis $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);

        if (!empty($_ENV['REDIS_PASSWORD'])) {
            $this->redis->auth($_ENV['REDIS_PASSWORD']);
        }
    }

    public function get(string $key): mixed
    {
        $serializedData = $this->redis->get($key);

        return $serializedData !== false ? unserialize($serializedData) : null;
    }

    public function set(string $key, mixed $data, int $cacheLifeInSeconds = -1): void
    {
        if ($cacheLifeInSeconds > 0) {
            $this->redis->setex($key, $cacheLifeInSeconds, serialize($data));
        } else {
            $this->redis->set($key, serialize($data));
        }
    }
}
