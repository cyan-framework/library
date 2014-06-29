<?php
namespace Cyan\Library;

/**
 * Class Factory
 * @package Cyan\Library
 */
abstract class Factory
{
    /**
     * @var \ArrayObject
     */
    protected $_registry;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->_registry = new \ArrayObject();
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    protected function get($key, $default = null)
    {
        return isset($this->$key) ? $this->$key : $default ;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->_registry[$key];
    }

    /**
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->_registry[$key]);
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->_registry[$key] = $value;
    }
}