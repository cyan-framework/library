<?php
namespace Cyan\Library;

trait TraitMultiton
{
    /**
     * Singleton instances accessible by array key
     */
    protected static $instances = [];
    /**
     * Return an instance of the class.
     * @param string $name
     */
    public static function getInstance($name = null)
    {
        $args = array_slice(func_get_args(), 1);
        $name = $name ?: 'default';
        $static = get_called_class();
        $key = sprintf('%s::%s', $static, $name);
        if(!array_key_exists($key, static::$instances))
        {
            $ref = new ReflectionClass($static);
            $ctor = is_callable($ref, '__construct');

            switch (count($args)) {
                case 0:
                    $instance = new $static;
                    break;
                case 1:
                    $instance = new $static($args[0]);
                    break;
                case 2:
                    $instance = new $static($args[0], $args[1]);
                    break;
                case 3:
                    $instance = new $static($args[0], $args[1], $args[2]);
                    break;
                default:
                    $instance = call_user_func_array($static,$args);
                    break;
            }


            static::$instances[$key] = $instance;
        }

        return static::$instances[$key];
    }
}