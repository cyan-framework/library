<?php
namespace Cyan\Library;

/**
 * Class FactoryPlugin
 * @package Cyan\Library
 * @since 1.0.0
 */
class FactoryPlugin extends Factory
{
    /**
     * Create a Plugin
     *
     * @param string $type
     * @param string $name
     * @param $closure
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function create($type, $name, $closure = null)
    {
        if ($closure instanceof \Closure) {
            $plugin = new Plugin($closure);
        } elseif ($closure instanceof Plugin) {
            $plugin = $closure;
        } else {
            throw new FactoryException(sprintf('Error: %s is not a instance of Plugin',gettype($closure)));
        }

        $this->factory_registry[$type][$name] = $plugin;

        return $this;
    }

    /**
     * Alias for register
     *
     * @param string $type
     * @param string $name
     * @param Plugin $plugin
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function register($type, $name, Plugin $plugin)
    {
        $this->create($type, $name, $plugin);

        return $this;
    }

    /**
     * Return all plugin types
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getPluginTypes()
    {
        return array_keys($this->factory_registry);
    }

    /**
     * @param $type
     * @param TraitsEvent $target_object
     */
    public function assign($type, $target_object)
    {
        $type = strtolower($type);
        $required = __NAMESPACE__.'\TraitEvent';
        $reflection_class = new ReflectionClass($target_object);
        $parent = $reflection_class->getParentClass();

        $use = $reflection_class->getTraitNames();
        unset($reflection_class);
        if ($parent) {
            $use = array_merge($use, $parent->getTraitNames());
        }
        if (!in_array($required,$use)) {
            throw new FactoryException(sprintf('Cant assign %s Plugins to %s %s, because they are not use TraitEvent.',$type,get_class($target_object),gettype($target_object)));
        }
        if (!isset($this->factory_registry[$type]) || empty($this->factory_registry[$type])) return;
        foreach ($this->factory_registry[$type] as $plugin_name => $plugin) {
            $target_object->registerEventPlugin($plugin_name, $plugin);
        }
    }
}