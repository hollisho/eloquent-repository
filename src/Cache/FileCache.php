<?php
namespace hollisho\repository\Cache;

class FileCache implements CacheInterface
{
    /**
     * @var string
     */
    protected $cachePath;

    /**
     * FileCache constructor.
     * @param string $cachePath
     */
    public function __construct(string $cachePath)
    {
        $this->cachePath = rtrim($cachePath, '/');
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
    }

    /**
     * 获取缓存文件路径
     * @param string $key
     * @return string
     */
    protected function getCacheFile(string $key): string
    {
        return $this->cachePath . '/' . md5($key) . '.cache';
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, $default = null)
    {
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return $default;
        }

        $content = file_get_contents($file);
        $data = unserialize($content);
        
        // 检查是否过期
        if (isset($data['ttl']) && $data['ttl'] < time()) {
            $this->delete($key);
            return $default;
        }
        
        return $data['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $file = $this->getCacheFile($key);
        $data = [
            'value' => $value,
            'ttl' => $ttl ? (time() + $ttl) : null
        ];
        
        return file_put_contents($file, serialize($data)) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        $file = $this->getCacheFile($key);
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $files = glob($this->cachePath . '/*.cache');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        $file = $this->getCacheFile($key);
        if (!file_exists($file)) {
            return false;
        }

        $content = file_get_contents($file);
        $data = unserialize($content);
        
        // 检查是否过期
        if (isset($data['ttl']) && $data['ttl'] < time()) {
            $this->delete($key);
            return false;
        }
        
        return true;
    }
} 