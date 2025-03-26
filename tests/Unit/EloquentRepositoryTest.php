<?php

namespace hollisho\repository\tests\Unit;

use hollisho\repository\Cache\CacheInterface;
use hollisho\repository\Cache\RedisCache;
use hollisho\repository\EloquentRepository;
use hollisho\repository\Query\QueryObject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class TestModel extends Model
{
    protected $fillable = ['name', 'email'];
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

class EloquentRepositoryTest extends TestCase
{
    private $repository;
    private $container;
    private $cache;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->container = $this->createMock(ContainerInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        
        $this->repository = new TestRepository();
        $this->repository->setContainer($this->container);
        $this->repository->setCache($this->cache);
        $this->repository->setCacheTtl(3600);
    }
    
    public function testFindWithCache()
    {
        $model = new TestModel();
        $model->id = 1;
        $model->name = 'Test User';
        
        $this->container->expects($this->once())
            ->method('get')
            ->with(TestModel::class)
            ->willReturn($model);
            
        $this->cache->expects($this->once())
            ->method('has')
            ->willReturn(false);
            
        $this->cache->expects($this->once())
            ->method('set')
            ->willReturn(true);
            
        $result = $this->repository->find(1);
        
        $this->assertEquals($model, $result);
    }
    
    public function testQueryObject()
    {
        $query = new TestQueryObject();
        $query->addCriteria('name', 'John')
            ->addCriteria('email', 'john@example.com');
            
        $builder = $this->createMock(Builder::class);
        $builder->expects($this->exactly(2))
            ->method('where')
            ->willReturnSelf();
            
        $model = $this->createMock(TestModel::class);
        $model->expects($this->once())
            ->method('query')
            ->willReturn($builder);
            
        $this->container->expects($this->once())
            ->method('get')
            ->with(TestModel::class)
            ->willReturn($model);
            
        $this->repository->makeModel();
        $this->repository->applyQuery($query);
    }
} 