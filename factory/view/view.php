<?php
namespace Cyan\Library;

/**
 * Class FactoryView
 * @package Cyan\Library
 */
class FactoryView extends Factory
{
    use TraitsSingleton;

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