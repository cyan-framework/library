<?php
namespace Cyan\Library;

/**
 * Class FactoryApplication
 * @package Cyan\Library
 */
class FactoryApplication extends Factory
{
    /**
     * Singleton Instance
     *
     * @var self
     */
    private static $instance;

    /**
     * @var Application
     */
    public $current;

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
     * Create new Application if not exists
     *
     * @param $name
     * @return Application
     */
    public function create()
    {
        $args = func_get_args();
        $name = isset($args[0]) ? $args[0] : null ;

        //create default name
        if (!is_string($name)) {
            foreach (debug_backtrace() as $debug) {
                if (sprintf('%s::%s',$debug['class'],$debug['function']) == __METHOD__) {
                    $name = basename(dirname($debug['file']));
                    break;
                }
            }
        }

        if (isset($this->$name)) {
            throw new ApplicationException(sprintf('An application named "%s" is already defined.',$name));
        }

        if (!isset($this->$name)) {
            switch (count($args)) {
                case 2:
                    if (!is_string($args[0]) && !is_callable($args[1])) {
                        throw new ApplicationException('Invalid argument orders. Spected (String, Closure) given (%s,%s).',gettype($args[0]),gettype($args[1]));
                    }
                    $name = $args[0];
                    $initialize = $args[1];
                    $this->current = new Application($name, $initialize);
                    break;
                case 1:

                    if (is_callable($args[0])) {
                        $this->current = new Application($name,$args[0]);
                    } elseif (is_string($args[0])) {
                        $this->current = new Application($args[0]);
                    } else {
                        throw new ApplicationException('Invalid argument type! Spected String or Closure, "%s" given.',gettype($args[0]));
                    }
                    break;
                case 0:
                    $this->current = new Application($name);
                    break;
                default:
                    throw new ApplicationException('Invalid arguments. Spected (String, Closure).');
                    break;
            }
            $this->current->initialize();

            $this->$name = $this->current;
        }
        $this->current = $this->$name;

        return $this->current;
    }

    /**
     * @param $name
     * @return Application
     */
    public function get($name, $default = null)
    {
        return $this->get($name, $default);
    }

    /**
     * Check if isset
     *
     * @param $name
     * @return bool
     */
    public function exists($name)
    {
        return isset($this->$name);
    }

    /**
     * List of Applications
     *
     * @return \ArrayObject
     */
    public function all()
    {
        return $this->_registry;
    }
}