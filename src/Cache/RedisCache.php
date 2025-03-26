<?php
namespace hollisho\repository\Cache;

use Predis\Client;

class RedisCache implements CacheInterface
{
    /**
     * @var Client
     */
    protected $redis;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * RedisCache constructor.
     * @param Client $redis
     * @param string $prefix
     */
    public function __construct(Client $redis, string $prefix = 'repository:')
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
    }

    /**
     * 获取完整的缓存键名
     * @param string $key
     * @return string
     */
    protected function getCacheKey(string $key): string
    {
        return $this->prefix . $key;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, $default = null)
    {
        $value = $this->redis->get($this->getCacheKey($key));
        return $value !== null ? unserialize($value) : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $key = $this->getCacheKey($key);
        $value = serialize($value);
        
        if ($ttl === null) {
            return $this->redis->set($key, $value) === true;
        }
        
        return $this->redis->setex($key, $ttl, $value) === true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        return (bool)$this->redis->del($this->getCacheKey($key));
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $keys = $this->redis->keys($this->prefix . '*');
        if (empty($keys)) {
            return true;
        }
        return (bool)$this->redis->del($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return (bool)$this->redis->exists($this->getCacheKey($key));
    }
} 