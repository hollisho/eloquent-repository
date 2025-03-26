<?php
namespace hollisho\repository\Cache;

class MemcachedCache implements CacheInterface
{
    /**
     * @var \Memcached
     */
    protected $memcached;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * MemcachedCache constructor.
     * @param \Memcached $memcached
     * @param string $prefix
     */
    public function __construct(\Memcached $memcached, string $prefix = 'repository:')
    {
        $this->memcached = $memcached;
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
        $value = $this->memcached->get($this->getCacheKey($key));
        return $this->memcached->getResultCode() === \Memcached::RES_SUCCESS ? $value : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        return $this->memcached->set(
            $this->getCacheKey($key),
            $value,
            $ttl ?? 0
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        return $this->memcached->delete($this->getCacheKey($key));
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        return $this->memcached->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        $this->memcached->get($this->getCacheKey($key));
        return $this->memcached->getResultCode() === \Memcached::RES_SUCCESS;
    }
} 