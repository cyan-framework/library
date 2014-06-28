<?php
namespace Cyan\Library;

/**
 * Class TraitsEvent
 * @package Cyan\Library
 */
trait TraitsEvent
{
    /**
     * List of Plugins
     *
     * @var \ArrayObject
     */
    protected $_plugins;

    /**
     * Dispatch a Event
     *
     * @return self
     */
    public function trigger()
    {
        if (empty($this->_plugins)) return $this;

        $args = func_get_args();

        $method = 'on' . array_shift($args);
        foreach ($this->_plugins as $name => $plugin) {
            $hasMethod = (method_exists($plugin,$method) || (isset($plugin->$method) && is_callable($plugin->$method)));
            if (!$hasMethod) continue;
            call_user_func_array(array($plugin, $method),$args);
        }

        return $this;
    }

    /**
     * @return self
     */
    public function registerPlugin($name, Plugin $plugin)
    {
        $this->_plugins[$name] = $plugin;

        return $this;
    }

    /**
     * @return self
     */
    public function removePlugin($name)
    {
        unset($this->_plugins[$name]);

        return $this;
    }

    /**
     * Get All Plugins
     *
     * @return \ArrayObject
     */
    public function getPlugins()
    {
        return $this->_plugins;
    }

    /**
     * Get Specific Plugin
     *
     * @param $name
     */
    public function getPlugin($name)
    {
        return $this->_plugins[$name];
    }
}

