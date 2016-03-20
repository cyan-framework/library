<?php
namespace Cyan\Library;

/**
 * Class Controller
 * @package Cyan\Library
 * @since 1.0.0
 */
class Controller
{
    use TraitPrototype, TraitContainer, TraitEvent;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $name;

    /**
     * Array Settings
     *
     * @var array
     * @since 1.0.0
     */
    private $settings = [];

    /**
     * @var \Cyan
     */
    protected $Cyan;

    /**
     * Controller Constructor
     *
     * @param $controller_name
     * @param array $controller_settings
     * @param $closure
     *
     * @since 1.0.0
     */
    public function __construct($controller_name = '', array $controller_settings = [], \Closure $closure = null)
    {
        $this->Cyan = \Cyan::initialize();
        $this->name = $controller_name;
        $this->settings = $controller_settings;

        if (!empty($closure) && is_callable($closure)) {
            call_user_func($closure->bindTo($this, $this));
        }
    }

    /**
     * Void
     *
     * @since 1.0.0
     */
    public function initialize()
    {
        $factory_plugin = $this->getContainer('factory_plugin');
        $factory_plugin->assign('controller', $this);
    }

    /**
     * Return Settings Array
     *
     * @param string $key
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getSettings($key = null)
    {
        return (isset($this->settings[$key]) && !is_null($key)) ? $this->settings[$key] : $this->settings;
    }

    /**
     * Return name
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getName()
    {
        if (empty($this->name)) {
            $reflection = new ReflectionClass(__CLASS__);
            $self_class_name = strtolower($reflection->getShortName());

            $class_parts = explode('_', Inflector::underscore(get_called_class()));
            $class_name = implode(array_slice($class_parts, array_search($self_class_name,$class_parts) + 1));

            if (empty($class_name)) {
                $remove_parts = [];
                $remove_parts[] = $self_class_name;
                $remove_parts[] = strtolower($this->getContainer('application')->getName());
                $class_name = implode(array_unique(array_diff($class_parts, $remove_parts)));
            }

            $this->name = $class_name;
        }
        
        return $this->name;
    }

    /**
     * Load file
     *
     * @since 1.0.0
     */
    public function lazyLoad()
    {
        if (isset($this->settings['file'])) {
            /** @var \Cyan $Cyan */
            $Cyan = $this->Cyan;
            /** @var self $controller */
            $controller = $this;
            require_once $this->settings['file'];
        }
    }

    /**
     * Extend a controller with closure
     *
     * @param $closure
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function extend(\Closure $closure = null)
    {
        if (!empty($closure) && is_callable($closure)) {
            call_user_func($closure->bindTo($this, $this));
        }

        return $this;
    }

    /**
     * Run dispatcher
     *
     * @return string|mixed
     *
     * @since 1.0.0
     * @throws TraitsException
     */
    public function run()
    {
        if (func_num_args() == 0) {
            throw new ControllerException('Please send a config to dispatcher');
        }
        $this->trigger('BeforeRun', $this);

        $arguments = func_get_args();
        $method_name = array_shift($arguments);

        if (is_callable([$this, $method_name])) {
            switch (count($arguments)) {
                case 0:
                    $return = $this->$method_name();
                    break;
                case 1:
                    $return = $this->$method_name($arguments[0]);
                    break;
                case 2:
                    $return = $this->$method_name($arguments[0], $arguments[1]);
                    break;
                case 3:
                    $return = $this->$method_name($arguments[0], $arguments[1], $arguments[2]);
                    break;
                case 4:
                    $return = $this->$method_name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                    break;
                default:
                    $return = call_user_func_array([$this, $method_name], $arguments);
                    break;
            }

            return $return;
        } else {
            $return = $this->__call($method_name, $arguments);
        }

        $this->trigger('AfterRun', $this);

        return $return;
    }
}