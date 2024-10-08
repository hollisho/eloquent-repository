<?php

namespace hollisho\repository;

use Psr\Container\ContainerInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

    public function __construct()
    {
        $this->makeModel();
    }

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
     * @return Model
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = $this->getContainer()->make($this->model());

        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * @param $column
     * @param $key
     * @return mixed
     * @author Hollis
     */
    public function lists($column, $key = null)
    {
        return $this->model->lists($column, $key);
    }

    public function all($columns = ['*'])
    {
        if ($this->model instanceof Builder) {
            $results = $this->model->get($columns);
        } else {
            $results = $this->model->all($columns);
        }
        return $results;
    }

    public function count(array $where = [], $columns = '*')
    {
        if ($where) {
            $this->applyConditions($where);
        }

        return $this->model->count();
    }

    public function paginate($limit = null, $columns = ['*'], $method = "paginate")
    {
        $limit = is_null($limit) ?: 15;
        $results = $this->model->{$method}($limit, $columns);
        $results->appends($this->getContainer()->make('request')->query());
        return $results;
    }

    public function find($id, $columns = ['*'])
    {
        return $this->model->findOrFail($id, $columns);
    }

    public function findByField($field, $value, $columns = ['*'])
    {
        return $this->model->where($field, '=', $value)->get($columns);
    }

    public function findWhere(array $where, $columns = ['*'])
    {
        $this->applyConditions($where);

        return $this->model->get($columns);
    }

    public function findWhereIn($field, array $values, $columns = ['*'])
    {
        // TODO: Implement findWhereIn() method.
    }

    public function findWhereNotIn($field, array $values, $columns = ['*'])
    {
        // TODO: Implement findWhereNotIn() method.
    }

    public function findWhereBetween($field, array $values, $columns = ['*'])
    {
        // TODO: Implement findWhereBetween() method.
    }

    public function create(array $attributes)
    {
        // TODO: Implement create() method.
    }

    public function update(array $attributes, $id)
    {
        // TODO: Implement update() method.
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        // TODO: Implement updateOrCreate() method.
    }

    public function delete($id)
    {
        // TODO: Implement delete() method.
    }

    public function deleteWhere(array $where)
    {
        // TODO: Implement deleteWhere() method.
    }

    public function orderBy($column, $direction = 'asc')
    {
        // TODO: Implement orderBy() method.
    }

    public function with($relations)
    {
        // TODO: Implement with() method.
    }

    public function whereHas($relation, $closure)
    {
        // TODO: Implement whereHas() method.
    }

    public function withCount($relations)
    {
        // TODO: Implement withCount() method.
    }

    public function firstOrNew(array $attributes = [])
    {
        // TODO: Implement firstOrNew() method.
    }

    public function firstOrCreate(array $attributes = [])
    {
        // TODO: Implement firstOrCreate() method.
    }

    public function limit($limit)
    {
        // TODO: Implement limit() method.
    }

    public function insert($values)
    {
        // TODO: Implement insert() method.
    }

    public function updateOrInsert(array $attributes, array $values = [])
    {
        // TODO: Implement updateOrInsert() method.
    }

    public static function __callStatic($method, $arguments)
    {
        // TODO: Implement __callStatic() method.
    }

    public function __call($method, $arguments)
    {
        // TODO: Implement __call() method.
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

}