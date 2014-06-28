<?php
namespace Cyan\Library;

/**
 * Trait Singleton
 */
trait TraitsMultiton
{
    /**
     * Array Instances
     *
     * @var array
     */
    protected static $_instances = array();

    /**
     * Multiton
     *
     * @param $id
     * @param array $config
     * @return self
     */
    public static function &getMultiton() {
        $args = func_get_args();
        $id = array_shift($args);
        $id = strtolower($id);

        if (!isset(self::$_instances[$id])) {
            $total_args = count($args);
            switch ($total_args)
            {
                case 0:
                    $instance = new self;
                    break;
                case 1:
                    $instance = new self($args[0]);
                    break;
                case 2:
                    $instance = new self($args[0], $args[1]);
                    break;
                case 3:
                    $instance = new self($args[0], $args[1], $args[2]);
                    break;
                default:
                    $instance = call_user_func_array(array(new self), $args);
                    break;

            }
            self::$_instances[$id] = $instance;
        }

        return self::$_instances[$id];
    }

    /**
     * List of Multitons
     *
     * @return array
     */
    public function getMultitons()
    {
        return self::$_instances;
    }
}