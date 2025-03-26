<?php
namespace hollisho\repository\Cache;

use Illuminate\Redis\RedisManager;

class CacheFactory
{
    /**
     * 创建Redis缓存
     * @param RedisManager $redis
     * @param string $prefix
     * @return RedisCache
     */
    public static function createRedisCache(RedisManager $redis, string $prefix = 'repository:'): RedisCache
    {
        return new RedisCache($redis, $prefix);
    }

    /**
     * 创建文件缓存
     * @param string $cachePath
     * @return FileCache
     */
    public static function createFileCache(string $cachePath): FileCache
    {
        return new FileCache($cachePath);
    }

    /**
     * 创建Memcached缓存
     * @param array $servers
     * @param string $prefix
     * @return MemcachedCache
     */
    public static function createMemcachedCache(array $servers = [['127.0.0.1', 11211]], string $prefix = 'repository:'): MemcachedCache
    {
        $memcached = new \Memcached();
        $memcached->addServers($servers);
        return new MemcachedCache($memcached, $prefix);
    }
} 