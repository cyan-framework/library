<?php
namespace Cyan\Library;

/**
 * Class Application
 * @package Cyan\Library
 */
class Application
{
    use TraitsPrototype, TraitsEvent;

    /**
     * Application Name
     *
     * @var string
     */
    protected $_name;

    /**
     * Start off the number of deferrals at 1. This will be
     * decremented by the Application's own `initialize` method.
     *
     * @var int
     */
    protected $_readinessDeferrals = 1;

    /**
     * List of registries for application
     *
     * @var \ArrayObject
     */
    protected $_registry;

    /**
     * List set Data
     *
     * @var \ArrayObject
     */
    protected $_data;

    /**
     * Application Router
     *
     * @var Router
     */
    public $Router = false;

    /**
     * @var FactoryController
     */
    public $Controller = false;

    /**
     * @var FactoryView
     */
    public $View = false;

    /**
     * Application Theme
     *
     * @var Theme
     */
    public $Theme = false;

    /**
     * Application Constructor
     */
    public function __construct()
    {
        $args = func_get_args();

        switch (count($args)) {
            case 2:
                if (!is_string($args[0]) && !is_callable($args[1])) {
                    throw new ApplicationException('Invalid argument orders. Spected (String, Closure) given (%s,%s).',gettype($args[0]),gettype($args[1]));
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
                    throw new ApplicationException('Invalid argument type! Spected String/Closure, "%s" given.',gettype($args[0]));
                }
                break;
            case 0:
                break;
            default:
                throw new ApplicationException('Invalid arguments. Spected (String, Closure).');
                break;
        }

        //create default name
        if (!isset($name)) {
            throw new ApplicationException('You must send a name');
        }

        $this->_name = $name;
        $this->_registry = new \ArrayObject();
        $this->_data = new \ArrayObject();

        if (isset($initialize) && is_callable($initialize)) {
            $this->__initializer = $initialize->bindTo($this, $this);
            $this->__initializer();
        }

        if ($this->Controller == false) {
            $this->Controller = new FactoryController;
        }

        if ($this->View == false) {
            $this->View = new FactoryView;
        }

        if ($this->Router == false) {
            $router_config = Finder::getInstance()->getIdentifier('app:config.router', array());
            $router_name = sprintf('%sApplicationRoute', $this->getName());
            $router_factory = FactoryRouter::getInstance();
            $this->Router = $router_factory->get($router_name, $router_factory->create($router_name, $router_config));
        }

        $this->advanceReadiness();
    }

    /**
     * Default Application
     */
    public function initialize()
    {
        $this->Router->resource('index');
        $this->Router->setDefault('index');

        //import application plugins
        FactoryPlugin::getInstance()->assign('application', $this);

        $this->trigger('Initialize', $this);
    }

    /**
     * Read a register
     *
     * @param $name
     * @return mixed
     */
    public function getRegister($name)
    {
        return $this->_registry[$name];
    }

    /**
     * Read Application Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Define Application Name
     *
     * @param $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @param $name
     * @param $value
     */
    public function register($name, $value)
    {
        $this->_registry[$name] = $value;
    }

    /**
     * Increase readiness state
     */
    public function deferReadiness()
    {
        $this->_readinessDeferrals++;
    }

    /**
     * Decrease readiness state
     */
    public function advanceReadiness()
    {
        if ($this->_readinessDeferrals) {
            $this->_readinessDeferrals--;
        }

        $this->trigger('Ready', $this);
    }

    /**
     * Read Application Config
     *
     * @return Array
     */
    public function getConfig()
    {
        return Finder::getInstance()->getIdentifier('app:config.application', array());
    }

    /**
     * Run a application
     */
    public function run()
    {
        //import application plugins
        FactoryPlugin::getInstance()->assign('application', $this);

        $finder = Finder::getInstance();

        if ($this->_readinessDeferrals) {
            throw new ApplicationException(sprintf('%s Application its not ready to run!',$this->_name));
        }

        if ($this->Router === false) {
            throw new ApplicationException(sprintf('%s Application Router its not defined!',$this->_name));
        }

        if ($this->Router->countRoutes() == 0) {
            throw new ApplicationException(sprintf('%s Application Router not have any route.',$this->_name));
        }

        if ($this->Theme == false) {
            $theme_config = Finder::getInstance()->getIdentifier('app:config.theme', array());
            $this->Theme = new Theme($theme_config);
        }

        $this->trigger('BeforeRun', $this);

        $response = $this->Router->run();

        $this->trigger('AfterRun', $this);

        $this->Theme->set('outlet', $response);

        echo $this->Theme;
    }

    /**
     *
     *
     * @param $name
     */
    public function __get($name)
    {
        if (substr($name,-10) == 'Controller') {
            return $this->Controller->get($name);
        }
    }

    /**
     * Listen PHP Built in Server
     */
    public function listen()
    {
        if (php_sapi_name() == 'cli-server') {
            $this->run();
        }

        return $this;
    }
}