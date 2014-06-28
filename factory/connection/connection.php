<?php
namespace Cyan\Library;

/**
 * Class FactoryConnection
 * @package Cyan\Library
 */
class FactoryConnection extends Factory
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
    public function create($name, array $config = array())
    {
        if (!isset($this->$name)) {
            $this->$name = new Connection($name);
            if (!empty($config)) {
                $this->$name->setConfig($config);
            }
        }

        return $this->$name;
    }

    /**
     * @return Array
     */
    public function getConnections()
    {
        return $this->_registry;
    }

    /**
     * @param $name
     * @return null
     */
    public function getConnection($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasConnection($name)
    {
        return isset($this->$name);
    }
}