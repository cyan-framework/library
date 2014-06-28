<?php
namespace Cyan\Library;

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
            $this->_registry[$type] = array();
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
        $required = __NAMESPACE__.'\TraitsEvent';
        $use = class_uses($target_object);
        if (!isset($use[$required])) {
            throw new FactoryException(sprintf('Cant assign %s Plugins to %s %s, because they are not use TraitsEvent.',$type,$target_object,gettype($target_object)));
        }
        if (empty($this->_registry[$type])) return;
        foreach ($this->_registry[$type] as $plugin_name => $plugin) {
            $target_object->registerPlugin($plugin_name, $plugin);
        }
    }
}