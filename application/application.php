<?php
namespace Cyan\Library;

/**
 * Class Application
 * @package Cyan\Library
 */
class Application
{
    use TraitSingleton;

    private static $instances = [];

    /**
     * Create a new instance
     *
     * @return Application
     *
     * @since 1.0.0
     */
    public function create()
    {
        $args = func_get_args();
        $name = isset($args[0]) ? $args[0] : null ;
        //create default name
        if (!is_string($name)) {
            foreach (debug_backtrace() as $debug) {
                if (isset($debug['class']) && sprintf('%s::%s',$debug['class'],$debug['function']) == __METHOD__) {
                    $name = basename(dirname($debug['file']));
                    break;
                }
            }
        }

        if (isset(self::$instances[$name])) {
            throw new ApplicationException(sprintf('An application named "%s" is already defined.',$name));
        }

        if (!isset(self::$instances[$name])) {
            switch (count($args)) {
                case 2:
                    if (!is_string($args[0]) && !is_callable($args[1])) {
                        throw new ApplicationException('Invalid argument orders. Spected (String, Closure) given (%s,%s).',gettype($args[0]),gettype($args[1]));
                    }
                    $name = $args[0];
                    $initialize = $args[1];
                    self::$instances[$name] = new ApplicationWeb($name, $initialize);
                    break;
                case 1:
                    if (is_callable($args[0])) {
                        self::$instances[$name] = new ApplicationWeb($name,$args[0]);
                    } elseif (is_string($args[0])) {
                        self::$instances[$name] = new ApplicationWeb($args[0]);
                    } else {
                        throw new ApplicationException('Invalid argument type! Spected String or Closure, "%s" given.',gettype($args[0]));
                    }
                    break;
                case 0:
                    self::$instances[$name] = new ApplicationWeb($name);
                    break;
                default:
                    throw new ApplicationException('Invalid arguments. Spected (String, Closure).');
                    break;
            }

            return self::$instances[$name];
        }
    }
}