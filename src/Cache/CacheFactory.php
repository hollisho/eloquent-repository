<?php
namespace hollisho\repository\Cache;

use Predis\Client;

class CacheFactory
{
    /**
     * 创建Redis缓存
     * @param array $config Redis配置
     * @param string $prefix 缓存键前缀
     * @return RedisCache
     */
    public static function createRedisCache(array $config = [], string $prefix = 'repository:'): RedisCache
    {
        $redis = new Client($config);
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