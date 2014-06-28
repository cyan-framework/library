<?php
namespace Cyan\Library;

/**
 * Class FactoryRouter
 * @package Cyan\Library
 */
class FactoryRouter extends Factory
{
    /**
     * Singleton Instance
     *
     * @var self
     */
    private static $instance;

    /**
     * Singleton Instance
     *
     * @param array $config
     * @return self
     */
    public static function getInstance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @param $name
     * @param array $config
     * @return Router
     */
    public function create($name, array $config = array(), \Closure $closure = null)
    {
        if (!isset($this->$name)) {
            $this->$name = new Router($config, $closure);
        }

        return $this->$name;
    }

    /**
     * @return Array
     */
    public function getRouters()
    {
        return $this->_registry;
    }

    /**
     * Return a Router
     *
     * @param $name
     * @param null $default_value
     * @return null
     */
    public function getRouter($name, $default_value = null)
    {
        return $this->get($name, $default_value);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasRouter($name)
    {
        return isset($this->$name);
    }
}