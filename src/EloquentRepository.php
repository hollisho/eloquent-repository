<?php

namespace hollisho\repository;

use hollisho\repository\Events\RepositoryEntityCreated;
use hollisho\repository\Events\RepositoryEntityCreating;
use hollisho\repository\Events\RepositoryEntityDeleted;
use hollisho\repository\Events\RepositoryEntityDeleting;
use hollisho\repository\Events\RepositoryEntityUpdated;
use hollisho\repository\Events\RepositoryEntityUpdating;
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
        $result = $this->model->lists($column, $key);
        $this->resetRepository();
        return $result;
    }

    public function all($columns = ['*'])
    {
        if ($this->model instanceof Builder) {
            $results = $this->model->get($columns);
        } else {
            $results = $this->model->all($columns);
        }
        $this->resetRepository();
        return $results;
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
        $result = $this->model->findOrFail($id, $columns);
        $this->resetRepository();
        return $result;
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
     * @throws NotFoundExceptionInterface
     * @author TeamOne technical department
     */
    public function create(array $attributes)
    {
        $this->getContainer()->get('events')->dispatch(new RepositoryEntityCreating($this, $attributes));
        $model = $this->model->newInstance($attributes);
        $model->save();
        $this->resetRepository();
        $this->getContainer()->get('events')->dispatch(new RepositoryEntityCreated($this, $model));
        return $model;
    }

    /**
     * @param array $attributes
     * @param $id
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @author TeamOne technical department
     */
    public function update(array $attributes, $id)
    {
        $model = $this->model->findOrFail($id);
        $this->getContainer()->get('events')->dispatch(new RepositoryEntityUpdating($this, $model));
        $model->fill($attributes);
        $model->save();
        $this->resetRepository();
        $this->getContainer()->get('events')->dispatch(new RepositoryEntityUpdated($this, $model));
        return $model;
    }

    /**
     * @param array $attributes
     * @param array $values
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @author TeamOne technical department
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {

        $this->getContainer()->get('events')->dispatch(new RepositoryEntityCreating($this, $attributes));
        $model = $this->model->updateOrCreate($attributes, $values);
        $this->resetRepository();
        $this->getContainer()->get('events')->dispatch(new RepositoryEntityUpdated($this, $model));

        return $model;
    }

    /**
     * @param $id
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @author TeamOne technical department
     */
    public function delete($id)
    {
        $model = $this->find($id);
        $originalModel = clone $model;
        $this->resetRepository();
        $this->getContainer()->get('events')->dispatch(new RepositoryEntityDeleting($this, $model));
        $deleted = $model->delete();
        $this->getContainer()->get('events')->dispatch(new RepositoryEntityDeleted($this, $originalModel));

        return $deleted;
    }

    /**
     * @param array $where
     * @return bool|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @author TeamOne technical department
     */
    public function deleteWhere(array $where)
    {
        $this->applyConditions($where);
        $this->getContainer()->get('events')->dispatch(new RepositoryEntityDeleting($this, $this->model->getModel()));
        $deleted = $this->model->delete();
        $this->getContainer()->get('events')->dispatch(new RepositoryEntityDeleted($this, $this->model->getModel()));
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
     * @author TeamOne technical department
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

    public function firstOrNew(array $attributes = [])
    {
        // TODO: Implement firstOrNew() method.
    }

    public function firstOrCreate(array $attributes = [])
    {
        // TODO: Implement firstOrCreate() method.
    }

    /**
     * @param $limit
     * @return mixed
     * @author TeamOne technical department
     */
    public function limit($limit)
    {
        $results = $this->model->limit($limit);
        $this->resetRepository();
        return $results;
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

    protected function resetRepository()
    {
        $this->resetModel();
        if (method_exists($this, 'resetCriteria')) {
            $this->resetCriteria();
        }
    }

}