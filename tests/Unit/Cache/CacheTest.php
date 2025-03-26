<?php
namespace hollisho\repository\tests\Unit\Cache;

use hollisho\repository\Cache\CacheFactory;
use hollisho\repository\Cache\CacheInterface;
use hollisho\repository\Cache\FileCache;
use hollisho\repository\Cache\MemcachedCache;
use hollisho\repository\Cache\RedisCache;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class CacheTest extends TestCase
{
    private $redisCache;
    private $fileCache;
    private $memcachedCache;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // 使用 Predis Client Mock
        $redisMock = $this->createMock(Client::class);
        $redisMock->method('get')->willReturn(serialize('test_value'));
        $redisMock->method('set')->willReturn(true);
        $redisMock->method('del')->willReturn(1);
        $redisMock->method('exists')->willReturn(1);
        
        $this->redisCache = new RedisCache($redisMock);
        
        // 设置文件缓存
        $this->fileCache = new FileCache(sys_get_temp_dir() . '/repository_test');
        
        // 使用 Memcached Mock
        $memcachedMock = $this->createMock(\Memcached::class);
        $memcachedMock->method('get')->willReturn('test_value');
        $memcachedMock->method('set')->willReturn(true);
        $memcachedMock->method('delete')->willReturn(true);
        $memcachedMock->method('flush')->willReturn(true);
        $memcachedMock->method('getResultCode')->willReturn(\Memcached::RES_SUCCESS);
        
        $this->memcachedCache = new MemcachedCache($memcachedMock);
    }
    
    /**
     * @dataProvider cacheImplementationsProvider
     */
    public function testBasicOperations(CacheInterface $cache)
    {
        // 测试设置和获取
        $this->assertTrue($cache->set('test_key', 'test_value'));
        $this->assertEquals('test_value', $cache->get('test_key'));
        
        // 测试删除
        $this->assertTrue($cache->delete('test_key'));
        
        // 测试清除
        $this->assertTrue($cache->clear());
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
        
        // 删除测试缓存目录
        if (is_dir(sys_get_temp_dir() . '/repository_test')) {
            array_map('unlink', glob(sys_get_temp_dir() . '/repository_test/*.*'));
            rmdir(sys_get_temp_dir() . '/repository_test');
        }
        
        parent::tearDown();
    }
} 