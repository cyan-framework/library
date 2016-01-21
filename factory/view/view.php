<?php
namespace Cyan\Library;

/**
 * Class FactoryView
 * @package Cyan\Library
 * @since 1.0.0
 */
class FactoryView extends Factory
{
    /**
     * @param $name
     * @param array $config
     * @return View
     */
    public function create($name, array $config = [], $class_name = 'View')
    {
        if (!isset($this->$name)) {
            if (empty($config)) {
                $config = [
                    'tpl' => strtolower($name)
                ];
            }
            $this->__set($name,new $class_name($config));
        }

        return $this->$name;
    }
}