<?php
namespace Cyan\Library;

/**
 * Class ReflectionClass
 * @package Cyan\Library
 * @since 1.0.0
 */
class ReflectionClass extends \ReflectionClass
{
    /**
     * get all trait names
     *
     * @return array
     */
    public function getTraitNames()
    {
        $traits = parent::getTraitNames();

        if ($this->getParentClass()) {
            $parent = $this->getParentClass();
            $traits = array_merge($traits,$this->getParentClassTraits($this->getParentClass()));
        }

        return $traits;
    }

    private function getParentClassTraits($class)
    {
        $traits = $class->getTraitNames();
        if ($class->getParentClass()) {
            $traits = array_merge($traits,$this->getParentClassTraits($class->getParentClass()));
        }
        return $traits;
    }
}