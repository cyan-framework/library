<?php
namespace Cyan\Framework;

/**
 * Class Factory
 * @package Cyan\Framework
 * @since 1.0.0
 */
abstract class Factory
{
    /**
     * Array list of factory
     *
     * @var array
     * @since 1.0.0
     */
    protected $factory_registry = [];

    /**
     * Get a
     *
     * @param string $key
     * @param null $default
     *
     * @return null
     *
     * @since 1.0.0
     */
    public function get($key, $default = null)
    {
        return isset($this->factory_registry[$key]) ? $this->factory_registry[$key] : $default ;
    }

    /**
     * @param string $key
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function __get($key)
    {
        return isset($this->factory_registry[$key]) ? $this->factory_registry[$key] : null ;
    }

    /**
     * @param string $key
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function __isset($key)
    {
        return isset($this->factory_registry[$key]);
    }

    /**
     * @param string $key
     * @param $value
     *
     * @since 1.0.0
     */
    public function __set($key, $value)
    {
        $this->factory_registry[$key] = $value;
    }
}