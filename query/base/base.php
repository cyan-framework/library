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
     * List of parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Define parameters
     *
     * @param array $parameters
     *
     * @return $this
     */
    public function parameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }
}