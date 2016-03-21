<?php
namespace Cyan\Framework;

/**
 * Class TraitsContainer
 * @package Cyan\Framework
 * @since 1.0.0
 */
trait TraitContainer
{
    /**
     * List of Containers
     *
     * @var array
     * @since 1.0.0
     */
    protected $containers = [];

    /**
     * Remove a container
     *
     * @param string $name
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function removeContainer($name)
    {
        unset($this->containers[$name]);

        return $this;
    }

    /**
     * Get All Containers
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getContainers()
    {
        return array_keys($this->containers);
    }

    /**
     * Get Specific Container
     *
     * @param $name
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function getContainer($name)
    {
        if (!isset($this->containers[$name])) {
            throw new TraitException(sprintf("Container %s not found in %s",$name,get_class($this)));
        }

        return $this->containers[$name];
    }

    /**
     * Check if container exists
     *
     * @param $name
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function hasContainer($name)
    {
        return isset($this->containers[$name]);
    }

    /**
     * Register a Container
     *
     * @param string $name
     * @param object $value
     * @param bool $override
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setContainer($name, $value, $override = false)
    {
        if (isset($this->containers[$name]) && !$override) {
            throw new TraitException(sprintf("Container %s already defined in %s",$name,get_class($this)));
        }

        $this->containers[$name] = $value;

        return $this;
    }
}