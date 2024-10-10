<?php

namespace hollisho\repository\Events;

use hollisho\repository\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @author Hollis
 * Class RepositoryEventBase
 * @package hollisho\repository\Events
 * @desc
 */
abstract class RepositoryEventBase
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var string
     */
    protected $action;

    /**
     * @param RepositoryInterface $repository
     * @param $model
     */
    public function __construct(RepositoryInterface $repository, $model = null)
    {
        $this->repository = $repository;
        $this->model = $model;
    }

    /**
     * @return Model|mixed|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
}