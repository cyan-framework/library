<?php
namespace Cyan\Library;

/**
 * Trait Singleton
 */
trait TraitsSingleton
{
    /**
     * Singleton Instance
     *
     * @var self
     */
    private static $_instance;

    /**
     * Singleton Instance
     *
     * @param array $config
     * @return Singleton
     */
    public static function getInstance() {
        $args = func_get_args();
        if (!(self::$_instance instanceof self)) {
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
            self::$_instance = $instance;
        }
        return self::$_instance;
    }
}