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

        if (isset($initialize) && is_callable($initialize)) {
            $this->__initializer = $initialize->bindTo($this, $this);
            $this->__initializer();
        }

        if ($this->Router === false) {
            $router_config = Finder::getInstance()->getIdentifier('app:config.router', array());
            $router_name = sprintf('%sApplicationRoute', $this->getName());
            $router_factory = FactoryRouter::getInstance();
            $this->Router = $router_factory->getRouter($router_name, $router_factory->create($router_name, $router_config));
        }

        if ($this->Controller === false) {
            $this->Controller = new FactoryController;
        }

        //import application plugins
        FactoryPlugin::getInstance()->assign('application', $this);

        $this->trigger('Initialize', $this);

        $this->advanceReadiness();
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

        if ($this->Theme === false) {
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