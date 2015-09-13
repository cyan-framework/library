<?php
namespace Cyan\Library;

/**
 * Class FactoryDatabase
 * @package Cyan\Library
 */
class FactoryDatabase extends Factory
{
    /**
     * Singleton Instance
     *
     * @var self
     */
    private static $instance;

    /**
     * @var Database
     */
    public $current;

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
    public function create($name, array $config = [])
    {
        if (!isset($this->$name)) {
            $this->$name = new Database2($name);
            if (!empty($config)) {
                $this->$name->setConfig($config);
            }
        }

        if (!isset($this->current)) {
            $this->current = $this->$name;
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