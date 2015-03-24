<?php
namespace Cyan\Library;

/**
 * Class QueryBase
 * @package Cyan\Library
 */
abstract class QueryBase
{
    /**
     * Traits Statements
     *
     * @var array
     */
    protected $statements = [];

    /**
     * Array parameters
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Return String Query
     */
    abstract function getQuery();

    /**
     *
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}