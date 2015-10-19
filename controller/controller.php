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
     * Array Settings
     *
     * @var array
     */
    private $settings = [];

    /**
     * @param $name
     * @param array $settings
     * @param callable $closure
     */
    public function __construct($name, array $settings = [], \Closure $closure = null)
    {
        $this->name = $name;
        $this->settings = $settings;

        if (!empty($closure) && is_callable($closure)) {
            $this->__initialize = $closure->bindTo($this, $this);
            call_user_func($this->__initialize);
        }

        $this->initialize();
    }

    /**
     * Void
     */
    protected function initialize()
    {

    }

    /**
     * Return Settings Array
     *
     * @param null $key
     * @return array
     */
    public function getSettings($key = null)
    {
        return (isset($this->settings[$key]) && !is_null($key)) ? $this->settings[$key] : $this->settings;
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
        if (isset($this->settings['file'])) {
            /** @var \Cyan $Cyan */
            $Cyan = \Cyan::initialize();
            /** @var self $controller */
            $controller = $this;
            require_once $this->settings['file'];
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