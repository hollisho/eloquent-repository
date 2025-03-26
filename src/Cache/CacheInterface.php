<?php
namespace hollisho\repository\Cache;

interface CacheInterface
{
    /**
     * 获取缓存
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * 设置缓存
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return bool
     */
    public function set(string $key, $value, ?int $ttl = null): bool;

    /**
     * 删除缓存
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * 清除所有缓存
     * @return bool
     */
    public function clear(): bool;

    /**
     * 判断缓存是否存在
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;
} 