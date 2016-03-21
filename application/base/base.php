<?php
namespace Cyan\Framework;

abstract class ApplicationBase
{
    use TraitEvent, TraitContainer, TraitPrototype{
        __set as setPrototype;
    }

    /**
     * Application Name
     *
     * @var string
     * @since 1.0.0
     */
    protected $name;

    /**
     * Cyan Instance
     *
     * @var \Cyan
     */
    protected $Cyan;

    /**
     * The list of helpers currently loaded
     *
     * @var array
     * @since 1.0.0
     */
    protected $helpers = [];

    /**
     * ApplicationWeb constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $args = func_get_args();
        switch (count($args)) {
            case 2:
                if (!is_string($args[0]) && !is_callable($args[1])) {
                    throw new ApplicationException('Invalid argument orders. (String, Closure) given (%s,%s).', gettype($args[0]), gettype($args[1]));
                }
                $name = $args[0];
                $initialize = $args[1];
                break;
            case 1:
                if (is_string($args[0])) {
                    $name = $args[0];
                } elseif (is_callable($args[0])) {
                    $initialize = $args[0];
                } else {
                    throw new ApplicationException('Invalid argument type! String|Closure, "%s" given.', gettype($args[0]));
                }
                break;
            case 0:
                break;
            default:
                throw new ApplicationException('Invalid arguments. Spected (String, Closure).');
                break;
        }

        //create default name
        if (empty($this->name) && !isset($name)) {
            $reflection_class = new ReflectionClass($this);
            if (__CLASS__ != get_class($this) && strpos(get_class($this),'Application') !== false) {
                $name_parts = array_filter(explode('Application', $reflection_class->getShortName()));
                $name = strtolower($name_parts[0]);
            } else {
                throw new ApplicationException('You must send a name');
            }
        }

        if (empty($this->name)) {
            $this->name = $name;
        }
        $this->Cyan = \Cyan::initialize();

        if (isset($initialize) && is_callable($initialize)) {
            $this->__initializer = $initialize->bindTo($this, $this);
            $this->__initializer();
        }
    }

    /**
     * @since 1.0.0
     */
    public function initialize()
    {
        $this->trigger('Initialize', $this);
    }

    /**
     * Read Application Name
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Define Application Name
     *
     * @param $name
     *
     * @return $this
     *
     * @throws ApplicationWebException
     *
     * @since 1.0.0
     */
    public function setName($name)
    {
        if (!empty($this->name)) {
            $message = __CLASS__ . '::$name is immutable once $name is set';
            throw new ApplicationWebException($message);
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Read Application Config
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->Cyan->Finder->getIdentifier(sprintf('%s:config.application',$this->name), []);
    }

    /**
     * Return Helper
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws \CyanException
     *
     * @since 1.0.0
     */
    public function getHelper($name)
    {
        if (!isset($this->helpers[$name])) {
            $class_name = __NAMESPACE__.'\\'.$name;
            $reflection = new ReflectionClass($class_name);
            if (in_array('TraitSingleton',$reflection->getTraitNames()) || is_callable([$class_name,'getInstance'])) {
                $this->helpers[$name] = $class_name::getInstance();
            } else {
                $this->helpers[$name] = $reflection->newInstance();
            }
        }

        return $this->helpers[$name];
    }

    /**
     * Retrieve an Helper
     *
     * @param string $name The helper name
     *
     * @return Mixed The helper instance requested
     *
     * @since 1.0.0
     */
    public function __get($name) {
        return $this->getHelper($name);
    }

    /**
     * @param $name
     * @param $value
     *
     * @since 1.0.0
     */
    public function __set($name, $value)
    {
        if (isset($this->helpers[$name])) {
            throw new \CyanException(sprintf('Cyan\\%s already defined',$name));
        }

        return $this->setPrototype($name, $value);
    }

    public abstract function execute();
}