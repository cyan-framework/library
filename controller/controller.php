<?php
namespace Cyan\Library;

class Controller
{
    use TraitsDispatcher;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param $name
     * @param array $config
     * @param callable $closure
     */
    public function __construct($name, array $config = array(), \Closure $closure = null)
    {
        $this->name = $name;

        if (!empty($closure) && is_callable($closure)) {
            $this->__initialize = $closure->bindTo($this, $this);
            call_user_func($this->__initialize);
        }
    }

    /**
     * Return name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param callable $closure
     * @return $this
     */
    public function extend(\Closure $closure = null)
    {
        if (!empty($closure) && is_callable($closure)) {
            $this->_extend = $closure->bindTo($this, $this);
            call_user_func($this->_extend);
        }

        return $this;
    }
}