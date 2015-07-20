<?php
namespace Cyan\Library;

/**
 * Class FactoryPlugin
 * @package Cyan\Library
 */
class FactoryPlugin extends Factory
{
    /**
     * Singleton Instance
     *
     * @var self
     */
    private static $instance;

    /**
     * Singleton Instance
     *
     * @param array $config
     * @return self
     */
    public static function getInstance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Create a Plugin
     *
     * @param $type
     * @param $name
     * @param callable $closure
     * @return $this
     */
    public function create($type, $name, \Closure $closure = null)
    {
        if (!isset($this->_registry[$type])) {
            $this->_registry[$type] = [];
        }

        $this->_registry[$type][$name] = new Plugin($closure);

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