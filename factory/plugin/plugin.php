<?php
namespace Cyan\Library;

/**
 * Class FactoryPlugin
 * @package Cyan\Library
 */
class FactoryPlugin extends Factory
{
    use TraitsSingleton;

    /**
     * Create a Plugin
     *
     * @param $type
     * @param $name
     * @param $closure
     * @return $this
     */
    public function create($type, $name, $closure = null)
    {
        if (!isset($this->_registry[$type])) {
            $this->_registry[$type] = [];
        }

        if ($closure instanceof \Closure) {
            $plugin = new Plugin($closure);
        } elseif ($closure instanceof Plugin) {
            $plugin = $closure;
        } else {
            throw new FactoryException(sprintf('Error: %s is not a instance of Plugin',gettype($closure)));
        }

        $this->_registry[$type][$name] = $plugin;

        return $this;
    }

    /**
     * Register Already Existent Plugin
     *
     * @param $type
     * @param $name
     * @param Plugin $plugin
     * @return $this
     */
    public function register($type, $name, Plugin $plugin)
    {
        $this->_registry[$type][$name] = $plugin;

        return $this;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        $types = [];
        foreach ($this->_registry as $type => $plugin) {
            $types[] = $type;
        }

        return $types;
    }

    /**
     * @param $type
     * @param TraitsEvent $target_object
     */
    public function assign($type, $target_object)
    {
        $type = strtolower($type);
        $required = __NAMESPACE__.'\TraitsEvent';
        $rf = new \ReflectionClass($target_object);
        $parent = $rf->getParentClass();

        $use = $rf->getTraitNames();
        if ($parent) {
            $use = array_merge($use, $parent->getTraitNames());
        }
        if (!in_array($required,$use)) {
            throw new FactoryException(sprintf('Cant assign %s Plugins to %s %s, because they are not use TraitsEvent.',$type,get_class($target_object),gettype($target_object)));
        }
        if (empty($this->_registry[$type])) return;
        foreach ($this->_registry[$type] as $plugin_name => $plugin) {
            $target_object->registerEventPlugin($plugin_name, $plugin);
        }
    }
}