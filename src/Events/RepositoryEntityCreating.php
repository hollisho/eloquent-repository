<?php

namespace hollisho\repository\Events;

class RepositoryEntityCreating extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "creating";
}