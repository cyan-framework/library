<?php
namespace Cyan\Library;

/**
 * Class Controller
 * @package Cyan\Library
 */
class Controller
{
    use TraitsDispatcher, TraitsContainer;

    /**
     * @var string
     */
    protected $name;

    /**
     * Array Config
     *
     * @var array
     */
    public $config = [];

    /**
     * @param $name
     * @param array $config
     * @param callable $closure
     */
    public function __construct($name, array $config = [], \Closure $closure = null)
    {
        $this->name = $name;
        $this->config = $config;

        if (!empty($closure) && is_callable($closure)) {
            $this->__initialize = $closure->bindTo($this, $this);
            call_user_func($this->__initialize);
        }
    }

    /**
     * Return Config Array
     *
     * @param null $key
     * @return array
     */
    public function getConfig($key = null)
    {
        return (isset($this->config[$key]) && !is_null($key)) ? $this->config[$key] : $this->config;
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
     * Load file
     */
    public function lazyLoad()
    {
        if (isset($this->config['file'])) {
            /** @var \Cyan $Cyan */
            $Cyan = \Cyan::initialize();
            /** @var self $controller */
            $controller = $this;
            require_once $this->config['file'];
        }
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