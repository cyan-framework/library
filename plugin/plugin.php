<?php
namespace Cyan\Framework;

/**
 * Class Plugin
 * @package Cyan\Framework
 * @since 1.0.0
 */
class Plugin
{
    use TraitPrototype;

    /**
     * Plugin constructor.
     *
     * @param \Closure|null $closure
     *
     * @since 1.0.0
     */
    public function __construct(\Closure $closure = null)
    {
        //Add initialize
        if (is_callable($closure)) {
            $this->__initialize = $closure->bindTo($this, $this);
            $this->__initialize();
        }
    }
}