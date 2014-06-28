<?php
namespace Cyan\Library;

/**
 * Class Plugin
 * @package Cyan\Library
 */
class Plugin
{
    use TraitsPrototype;

    /**
     * Plugin Constructor
     *
     * @param callable $closure
     */
    public function __construct(\Closure $closure = null)
    {
        //Add initialize
        if (is_callable($closure)) {
            $this->_construct = $closure;
            $this->_construct();
        }
    }
}