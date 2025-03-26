<?php

namespace hollisho\repository\tests\Unit;

use hollisho\repository\Cache\CacheInterface;
use hollisho\repository\EloquentRepository;
use hollisho\repository\Query\QueryObject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class TestModel extends Model
{
    protected $fillable = ['name', 'email'];
    
    public function newQuery()
    {
        return new Builder($this->getMockBuilder(\Illuminate\Database\ConnectionInterface::class)->getMock());
    }
}

class TestRepository extends EloquentRepository
{
    public function model()
    {
        return TestModel::class;
    }
}

class TestQueryObject extends QueryObject
{
    public function apply(Builder $query): Builder
    {
        if ($name = $this->getCriteria('name')) {
            $query->where('name', 'like', "%{$name}%");
        }
        
        if ($email = $this->getCriteria('email')) {
            $query->where('email', '=', $email);
        }
        
        return $query;
    }
}

class TestContainer implements ContainerInterface
{
    private $instances = [];
    
    public function get($id)
    {
        return $this->instances[$id] ?? null;
    }
    
    public function has($id): bool
    {
        return isset($this->instances[$id]);
    }
    
    public function set($id, $instance)
    {
        $this->instances[$id] = $instance;
    }
}

class EloquentRepositoryTest extends TestCase
{
    private $repository;
    private $container;
    private $cache;
    private $model;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->model = new TestModel();
        
        $this->container = new TestContainer();
        $this->container->set(TestModel::class, $this->model);
        
        $this->cache = $this->createMock(CacheInterface::class);
        
        $this->repository = new TestRepository();
        $this->repository->setContainer($this->container);
        $this->repository->setCache($this->cache);
        $this->repository->setCacheTtl(3600);
        $this->repository->makeModel();
    }
    
    public function testFindWithCache()
    {
        $this->cache->expects($this->once())
            ->method('has')
            ->willReturn(false);
            
        $this->cache->expects($this->once())
            ->method('set')
            ->willReturn(true);
            
        $result = $this->repository->find(1);
        
        $this->assertInstanceOf(TestModel::class, $result);
    }
    
    public function testQueryObject()
    {
        $query = new TestQueryObject();
        $query->addCriteria('name', 'John')
            ->addCriteria('email', 'john@example.com');
            
        $this->repository->applyQuery($query);
        
        // 验证查询对象是否正确应用
        $this->assertInstanceOf(Builder::class, $this->repository->getQuery());
    }
} 