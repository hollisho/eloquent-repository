<?php

namespace hollisho\repository\Events;

/**
 * @author Hollis
 * Class RepositoryEntityCreated
 * @package hollisho\repository\Events
 * @desc
 */
class RepositoryEntityCreated extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "created";
}