<?php
namespace hollisho\repository\tests\Unit\Cache;

use hollisho\repository\Cache\CacheFactory;
use hollisho\repository\Cache\CacheInterface;
use hollisho\repository\Cache\FileCache;
use hollisho\repository\Cache\MemcachedCache;
use hollisho\repository\Cache\RedisCache;
use Illuminate\Redis\RedisManager;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{
    private $redisCache;
    private $fileCache;
    private $memcachedCache;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // 设置Redis缓存
        $redis = new RedisManager(null, 'predis', [
            'default' => [
                'host' => '127.0.0.1',
                'port' => 6379,
            ]
        ]);
        $this->redisCache = CacheFactory::createRedisCache($redis);
        
        // 设置文件缓存
        $this->fileCache = CacheFactory::createFileCache(__DIR__ . '/cache');
        
        // 设置Memcached缓存
        $this->memcachedCache = CacheFactory::createMemcachedCache();
    }
    
    /**
     * @dataProvider cacheImplementationsProvider
     */
    public function testBasicOperations(CacheInterface $cache)
    {
        // 测试设置和获取
        $this->assertTrue($cache->set('test_key', 'test_value'));
        $this->assertEquals('test_value', $cache->get('test_key'));
        
        // 测试TTL
        $this->assertTrue($cache->set('ttl_key', 'ttl_value', 1));
        $this->assertTrue($cache->has('ttl_key'));
        sleep(2);
        $this->assertFalse($cache->has('ttl_key'));
        
        // 测试删除
        $this->assertTrue($cache->set('delete_key', 'delete_value'));
        $this->assertTrue($cache->delete('delete_key'));
        $this->assertNull($cache->get('delete_key'));
        
        // 测试清除
        $this->assertTrue($cache->set('clear_key1', 'clear_value1'));
        $this->assertTrue($cache->set('clear_key2', 'clear_value2'));
        $this->assertTrue($cache->clear());
        $this->assertNull($cache->get('clear_key1'));
        $this->assertNull($cache->get('clear_key2'));
    }
    
    public function cacheImplementationsProvider()
    {
        return [
            'Redis Cache' => [$this->redisCache],
            'File Cache' => [$this->fileCache],
            'Memcached Cache' => [$this->memcachedCache]
        ];
    }
    
    protected function tearDown(): void
    {
        // 清理测试数据
        $this->redisCache->clear();
        $this->fileCache->clear();
        $this->memcachedCache->clear();
        
        parent::tearDown();
    }
} 