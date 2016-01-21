<?php
namespace Cyan\Library;

/**
 * Class QueryBase
 * @package Cyan\Library
 * @since 1.0.0
 */
abstract class DatabaseQueryBase
{
    /**
     * Traits Statements
     *
     * @var array
     * @since 1.0.0
     */
    protected $statements = [];

    /**
     * Array parameters
     *
     * @var array
     * @since 1.0.0
     */
    protected $parameters = [];

    /**
     * Return String Query
     * @since 1.0.0
     */
    abstract function getQuery();

    /**
     * List of parameters
     *
     * @return array
     * @since 1.0.0
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
     *
     * @since 1.0.0
     */
    public function parameters(array $parameters)
    {
        $this->parameters = !empty($this->parameters) ? array_merge($this->parameters, $parameters) : $parameters;

        return $this;
    }
}
