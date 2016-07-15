<?php
namespace Cyan\Framework;

/**
 * Class TraitsEvent
 * @package Cyan\Framework
 * @since 1.0.0
 */
trait TraitEvent
{
    /**
     * List of Plugins
     *
     * @var array
     * @since 1.0.0
     */
    protected $plugins;

    /**
     * Dispatch a Event
     *
     * @return self
     *
     * @since 1.0.0
     */
    public function trigger()
    {
        if (empty($this->plugins)) return null;
        $return = [];
        
        $args = func_get_args();

        $method = 'on' . array_shift($args);
        foreach ($this->plugins as $name => $plugin) {
            $hasMethod = (method_exists($plugin,$method) || (isset($plugin->$method) && is_callable($plugin->$method)));
            if (!$hasMethod) continue;
            switch (count($args)) {
                case 0:
                    $return[] = $plugin->$method();
                    break;
                case 1:
                    $return[] = $plugin->$method($args[0]);
                    break;
                case 2:
                    $return[] = $plugin->$method($args[0], $args[1]);
                    break;
                case 3:
                    $return[] = $plugin->$method($args[0], $args[1], $args[2]);
                    break;
                case 4:
                    $return[] = $plugin->$method($args[0], $args[1], $args[2], $args[3]);
                    break;
                default:
                    $return[] = call_user_func_array([$plugin, $method], $args);
                    break;
            }

        }

        return $return;
    }

    /**
     * Register a Plugin
     *
     * @param $name
     *
     * @param Plugin $plugin
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function registerEventPlugin($name, Plugin $plugin)
    {
        $this->plugins[$name] = $plugin;

        return $this;
    }

    /**
     * remove a plugin
     *
     * @param string $name
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function removeEventPlugin($name)
    {
        unset($this->plugins[$name]);

        return $this;
    }

    /**
     * Get All Plugins
     *
     * @return array
     */
    public function getEventPlugins()
    {
        return $this->plugins;
    }

    /**
     * Get Specific Plugin
     *
     * @param $name
     */
    public function getEventPlugin($name)
    {
        return $this->plugins[$name];
    }
}

