<?php
namespace hollisho\repository\Query;

use Illuminate\Database\Eloquent\Builder;

abstract class QueryObject
{
    /**
     * @var array
     */
    protected $criteria = [];

    /**
     * 应用查询条件
     * @param Builder $query
     * @return Builder
     */
    abstract public function apply(Builder $query): Builder;

    /**
     * 添加查询条件
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addCriteria(string $key, $value)
    {
        $this->criteria[$key] = $value;
        return $this;
    }

    /**
     * 获取查询条件
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getCriteria(string $key, $default = null)
    {
        return $this->criteria[$key] ?? $default;
    }

    /**
     * 获取所有查询条件
     * @return array
     */
    public function getAllCriteria(): array
    {
        return $this->criteria;
    }

    /**
     * 清除查询条件
     * @return $this
     */
    public function clearCriteria()
    {
        $this->criteria = [];
        return $this;
    }
} 