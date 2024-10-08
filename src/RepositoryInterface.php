<?php
namespace hollisho\repository;

/**
 * @author Hollis
 * Interface RepositoryInterface
 * @package hollisho\repository
 * @desc
 */
interface RepositoryInterface
{
    public function lists($column, $key = null);

    public function all($columns = ['*']);

    public function count(array $where = [], $columns = '*');

    public function paginate($limit = null, $columns = ['*'], $method = "paginate");

    public function find($id, $columns = ['*']);

    public function findByField($field, $value, $columns = ['*']);

    public function findWhere(array $where, $columns = ['*']);

    public function findWhereIn($field, array $values, $columns = ['*']);

    public function findWhereNotIn($field, array $values, $columns = ['*']);

    public function findWhereBetween($field, array $values, $columns = ['*']);

    public function create(array $attributes);

    public function update(array $attributes, $id);

    public function updateOrCreate(array $attributes, array $values = []);

    public function delete($id);

    public function deleteWhere(array $where);

    public function orderBy($column, $direction = 'asc');

    public function with($relations);

    public function whereHas($relation, $closure);

    public function withCount($relations);

    public function firstOrNew(array $attributes = []);

    public function firstOrCreate(array $attributes = []);

    public function limit($limit);

    public function insert($values);

    public function updateOrInsert(array $attributes, array $values = []);

    /**
     * Trigger static method calls to the model
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments);

    /**
     * Trigger method calls to the model
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments);
}