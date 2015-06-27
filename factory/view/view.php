<?php
namespace Cyan\Library;

/**
 * Class FactoryView
 * @package Cyan\Library
 */
class FactoryView extends Factory
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
     * @return View
     */
    public function create($name, array $config = [])
    {
        if (!isset($this->$name)) {
            if (empty($config)) {
                $config = [
                    'tpl' => strtolower($name)
                ];
            }
            $this->$name = new View($config);
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