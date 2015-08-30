<?php
namespace Cyan\Library;

/**
 * Class FactoryRouter
 * @package Cyan\Library
 */
class FactoryRouter extends Factory
{
    use TraitsSingleton;

    /**
     * @param $name
     * @param array $config
     * @return Router
     */
    public function create($name, array $config = [], \Closure $closure = null)
    {
        if (!isset($this->$name)) {
            $this->$name = new Router($config, $closure);
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