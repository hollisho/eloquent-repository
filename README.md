# Eloquent Repository

一个优雅的 Laravel Eloquent ORM 仓储模式实现，支持多种缓存引擎。

## 安装

```bash
composer require hollisho/eloquent-repository
```

## 特性

- 完整的仓储模式实现
  - 标准化的 CRUD 操作
  - 链式查询支持
  - 事件驱动
  - 领域模型映射

- 灵活的缓存支持
  - Redis 缓存
  - Memcached 缓存
  - 文件缓存
  - 统一的缓存接口
  - 可自定义缓存实现

- 查询对象支持
  - 可复用的查询逻辑
  - 条件组合
  - 链式调用

## 基本使用

### 1. 创建仓储类

```php
use hollisho\repository\EloquentRepository;

class UserRepository extends EloquentRepository
{
    public function model()
    {
        return User::class;
    }
}
```

### 2. 基本操作

```php
$repository = new UserRepository();

// 查找
$user = $repository->find(1);
$users = $repository->findWhere(['status' => 'active']);

// 创建
$user = $repository->create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// 更新
$repository->update(['status' => 'inactive'], $id);

// 删除
$repository->delete($id);
```

### 3. 配置缓存

```php
use hollisho\repository\Cache\CacheFactory;

// Redis 缓存
$redis = new RedisManager(/* 配置 */);
$cache = CacheFactory::createRedisCache($redis);
$repository->setCache($cache)
    ->setCacheTtl(3600); // 设置缓存时间为1小时

// Memcached 缓存
$cache = CacheFactory::createMemcachedCache([
    ['127.0.0.1', 11211] // 服务器配置
]);
$repository->setCache($cache);

// 文件缓存
$cache = CacheFactory::createFileCache(storage_path('cache/repository'));
$repository->setCache($cache);
```

### 4. 使用查询对象

```php
use hollisho\repository\Query\QueryObject;

class UserQueryObject extends QueryObject
{
    public function apply(Builder $query): Builder
    {
        if ($name = $this->getCriteria('name')) {
            $query->where('name', 'like', "%{$name}%");
        }
        
        if ($status = $this->getCriteria('status')) {
            $query->where('status', '=', $status);
        }
        
        return $query;
    }
}

// 使用查询对象
$query = new UserQueryObject();
$query->addCriteria('status', 'active')
    ->addCriteria('name', 'John');

$users = $repository->applyQuery($query)->all();
```

## 高级用法

### 1. 自定义缓存实现

```php
use hollisho\repository\Cache\CacheInterface;

class CustomCache implements CacheInterface
{
    public function get(string $key, $default = null)
    {
        // 实现获取缓存
    }

    public function set(string $key, $value, ?int $ttl = null): bool
    {
        // 实现设置缓存
    }

    public function delete(string $key): bool
    {
        // 实现删除缓存
    }

    public function clear(): bool
    {
        // 实现清除所有缓存
    }

    public function has(string $key): bool
    {
        // 实现检查缓存是否存在
    }
}
```

### 2. 事件监听

```php
$repository->setContainer($container); // 设置容器
$repository->setRepositoryId('user.repository'); // 设置仓储ID
```

## 注意事项

1. 缓存配置
   - Redis 缓存需要安装 `predis/predis` 包
   - Memcached 缓存需要安装 PHP Memcached 扩展
   - 文件缓存需要确保缓存目录有写入权限

2. 性能优化
   - 合理设置缓存过期时间
   - 适当使用查询对象组合条件
   - 避免过度使用 `clear()` 方法

## 许可证

MIT License
```

