<?php
namespace Cyan\Library;

/**
 * Class TraitsContainer
 * @package Cyan\Library
 */
trait TraitsContainer
{
    /**
     * List of Plugins
     *
     * @var \ArrayObject
     */
    protected $_containers = [];

    /**
     * Remove a container
     *
     * @return self
     */
    public function removeContainer($name)
    {
        unset($this->_containers[$name]);

        return $this;
    }

    /**
     * Get All Containers
     *
     * @return \ArrayObject
     */
    public function getContainers()
    {
        return $this->_containers;
    }

    /**
     * Get Specific Plugin
     *
     * @param $name
     */
    public function getContainer($name)
    {
        if (!isset($this->_containers[$name])) {
            throw new \RuntimeException(sprintf("Container %s not found in %s",$name,get_class($this)));
        }

        return $this->_containers[$name];
    }

    /**
     * Register a Container
     *
     * @param $name
     * @param $value
     * @param bool $override
     * @return $this
     */
    public function setContainer($name, $value, $override = false)
    {
        if (isset($this->_containers[$name]) && !$override) {
            throw new \RuntimeException(sprintf("Container %s alreadyu defined in %s",$name,get_class($this)));
        }

        $this->_containers[$name] = $value;

        return $this;
    }
}