<?php

namespace hollisho\repository;

use hollisho\repository\Cache\CacheInterface;
use hollisho\repository\Query\QueryObject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @author Hollis
 * Class EloquentRepository
 * @package hollisho\repository
 * @desc
 */
abstract class EloquentRepository implements RepositoryInterface
{

    /**
     * The IoC container instance.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The repository identifier.
     *
     * @var string
     */
    protected $repositoryId;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var CacheInterface|null
     */
    protected $cache;

    /**
     * @var int|null
     */
    protected $cacheTtl;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function setRepositoryId($repositoryId)
    {
        $this->repositoryId = $repositoryId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepositoryId(): string
    {
        return $this->repositoryId ?: static::class;
    }


    /**
     * @throws RepositoryException
     */
    public function resetModel()
    {
        $this->makeModel();
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract public function model();

    /**
     * @return EloquentRepository
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = $this->getContainer()->make($this->model());

        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        $this->model = $model;
        return $this;
    }


    /**
     * @return Builder
     */
    public function getQuery()
    {
        return $this->model::query();
    }

    /**
     * 设置缓存实例
     * @param CacheInterface $cache
     * @return $this
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * 设置缓存时间
     * @param int $ttl
     * @return $this
     */
    public function setCacheTtl(int $ttl)
    {
        $this->cacheTtl = $ttl;
        return $this;
    }

    /**
     * 生成缓存键
     * @param string $method
     * @param array $args
     * @return string
     */
    protected function generateCacheKey(string $method, array $args = []): string
    {
        return sprintf(
            '%s:%s:%s',
            $this->getRepositoryId(),
            $method,
            md5(serialize($args))
        );
    }

    /**
     * 从缓存中获取数据
     * @param string $method
     * @param array $args
     * @param callable $callback
     * @return mixed
     */
    protected function remember(string $method, array $args, callable $callback)
    {
        if (!$this->cache) {
            return $callback();
        }

        $key = $this->generateCacheKey($method, $args);
        
        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $result = $callback();
        $this->cache->set($key, $result, $this->cacheTtl);
        
        return $result;
    }

    /**
     * 应用查询对象
     * @param QueryObject $query
     * @return $this
     */
    public function applyQuery(QueryObject $query)
    {
        $this->model = $query->apply($this->model instanceof Builder ? $this->model : $this->model->query());
        return $this;
    }

    public function all($columns = ['*'])
    {
        return $this->remember(__FUNCTION__, func_get_args(), function () use ($columns) {
            if ($this->model instanceof Builder) {
                $results = $this->model->get($columns);
            } else {
                $results = $this->model->all($columns);
            }
            $this->resetRepository();
            return $results;
        });
    }

    public function count(array $where = [], $columns = '*')
    {
        if ($where) {
            $this->applyConditions($where);
        }

        $result = $this->model->count();
        $this->resetRepository();
        return $result;
    }

    public function paginate($limit = null, $columns = ['*'], $method = "paginate")
    {
        $limit = is_null($limit) ?: 15;
        $results = $this->model->{$method}($limit, $columns);
        $results->appends($this->getContainer()->make('request')->query());
        $this->resetRepository();
        return $results;
    }

    public function simplePaginate($limit = null, $columns = ['*'])
    {
        return $this->paginate($limit, $columns, "simplePaginate");
    }

    public function find($id, $columns = ['*'])
    {
        return $this->remember(__FUNCTION__, func_get_args(), function () use ($id, $columns) {
            $result = $this->model->findOrFail($id, $columns);
            $this->resetRepository();
            return $result;
        });
    }

    public function findByField($field, $value, $columns = ['*'])
    {
        $result = $this->model->where($field, '=', $value)->get($columns);
        $this->resetRepository();
        return $result;
    }

    public function findWhere(array $where, $columns = ['*'])
    {
        $this->applyConditions($where);
        $result = $this->model->get($columns);
        $this->resetRepository();
        return $result;
    }

    public function findWhereIn($field, array $values, $columns = ['*'])
    {
        $result = $this->model->whereIn($field, $values)->get($columns);
        $this->resetRepository();
        return $result;
    }

    public function findWhereNotIn($field, array $values, $columns = ['*'])
    {
        $result = $this->model->whereNotIn($field, $values)->get($columns);
        $this->resetRepository();
        return $result;
    }

    public function findWhereBetween($field, array $values, $columns = ['*'])
    {
        $result = $this->model->whereBetween($field, $values)->get($columns);
        $this->resetRepository();
        return $result;
    }

    /**
     * @param array $attributes
     * @return Model
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|RepositoryException
     * @author Hollis Ho
     */
    public function create(array $attributes)
    {
        $model = $this->model->create($attributes);
        $this->resetRepository();
        return $model;
    }

    /**
     * @param array $attributes
     * @param $id
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @author Hollis Ho
     */
    public function update(array $attributes, $id)
    {
        $model = $this->model->findOrFail($id);
        $model->fill($attributes);
        $model->save();
        $this->resetRepository();
        return $model;
    }

    /**
     * @param array $attributes
     * @param array $values
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @author Hollis Ho
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {

        $model = $this->model->updateOrCreate($attributes, $values);
        $this->resetRepository();

        return $model;
    }

    /**
     * @param $id
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @author Hollis Ho
     */
    public function delete($id)
    {
        $model = $this->find($id);
        $originalModel = clone $model;
        $this->resetRepository();
        $deleted = $model->delete();

        return $deleted;
    }

    /**
     * @param array $where
     * @return bool|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @author Hollis Ho
     */
    public function deleteWhere(array $where)
    {
        $this->applyConditions($where);
        $deleted = $this->model->delete();
        $this->resetRepository();

        return $deleted;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    /**
     * @param $relation
     * @return $this
     * @author Hollis Ho
     */
    public function has($relation)
    {
        $this->model = $this->model->has($relation);

        return $this;
    }

    public function with($relations)
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

    public function whereHas($relation, $closure)
    {
        $this->model = $this->model->whereHas($relation, $closure);

        return $this;
    }

    public function withCount($relations)
    {
        $this->model = $this->model->withCount($relations);
        return $this;
    }

    public function firstOrNew(array $attributes = [], array $values = [])
    {
        $model = $this->model->firstOrNew($attributes);
        $this->resetRepository();
        return $model;
    }

    public function firstOrCreate(array $attributes = [], array $values = [])
    {
        $model = $this->model->firstOrCreate($attributes);
        $this->resetRepository();
        return $model;
    }

    /**
     * @param $limit
     * @return mixed
     * @author Hollis Ho
     */
    public function limit($limit)
    {
        $results = $this->model->limit($limit);
        $this->resetRepository();
        return $results;
    }

    public function insert($values)
    {
        $model = $this->model->insert($values);
        $this->resetRepository();
        return $model;
    }

    public function updateOrInsert(array $attributes, array $values = [])
    {
        $model = $this->model->updateOrInsert($attributes, $values);
        $this->resetRepository();

        return $model;
    }

    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array([new static(), $method], $arguments);
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->model, $method], $arguments);
    }

    /**
     * Applies the given where conditions to the model.
     *
     * @param array $where
     * @return void
     */
    protected function applyConditions(array $where)
    {
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                list($field, $condition, $val) = $value;
                $this->model = $this->model->where($field, $condition, $val);
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }
    }

    /**
     * @return void
     * @throws RepositoryException
     * @author Hollis Ho
     */
    protected function resetRepository()
    {
        $this->resetModel();
        if (method_exists($this, 'resetCriteria')) {
            $this->resetCriteria();
        }
    }

}