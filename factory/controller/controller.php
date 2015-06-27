<?php
namespace Cyan\Library;

/**
 * Class FactoryController
 * @package Cyan\Library
 */
class FactoryController extends Factory
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
     * @return mixed
     */
    public function create($name, array $config = [], \Closure $closure = null)
    {
        if (!isset($this->$name)) {
            $this->$name = new Controller($name,$config, $closure);
        }

        return $this->$name;
    }

    /**
     * @return Array
     */
    public function all()
    {
        return $this->_registry;
    }

    /**
     * @param $name
     * @return bool
     */
    public function exists($name)
    {
        return isset($this->$name);
    }
}