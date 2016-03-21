<?php
namespace Cyan\Framework;

/**
 * Trait Singleton
 * @since 1.0.0
 */
trait TraitSingleton
{
    /**
     * Singleton Instance
     *
     * @var self
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Singleton Instance
     *
     * @return self
     *
     * @since 1.0.0
     */
    public static function getInstance() {
        $args = func_get_args();
        if (!(self::$instance instanceof self)) {
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
                    $instance = call_user_func_array([new self], $args);
                    break;

            }
            self::$instance = $instance;
        }
        return self::$instance;
    }
}
