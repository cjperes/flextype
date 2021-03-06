<?php

declare(strict_types=1);

namespace Flextype\App\Foundation\Cache;

use Doctrine\Common\Cache\RedisCache;
use Psr\Container\ContainerInterface;
use Redis;
use RedisException;

class RedisCacheAdapter implements CacheAdapterInterface
{
    /**
     * Flextype Dependency Container
     *
     * @access private
     */
    private $flextype;
    
    public function __construct(ContainerInterface $flextype)
    {
        $this->flextype = $flextype;
    }

    public function getDriver() : object
    {
        $redis    = new Redis();
        $socket   = $this->flextype['registry']->get('flextype.settings.cache.redis.socket', false);
        $password = $this->flextype['registry']->get('flextype.settings.cache.redis.password', false);

        if ($socket) {
            $redis->connect($socket);
        } else {
            $redis->connect(
                $this->flextype['registry']->get('flextype.settings.cache.redis.server', 'localhost'),
                $this->flextype['registry']->get('flextype.settings.cache.redis.port', 6379)
            );
        }

        // Authenticate with password if set
        if ($password && ! $redis->auth($password)) {
            throw new RedisException('Redis authentication failed');
        }

        $driver = new RedisCache();
        $driver->setRedis($redis);

        return $driver;
    }
}
