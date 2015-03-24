<?php
namespace Cyan\Library;

/**
 * Class ApplicationApi
 * @package Cyan\Library
 */
class ApplicationApi
{
    use TraitsPrototype, TraitsEvent;

    /**
     * Api Name
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

        if ($this->Router == false) {
            $router_config = Finder::getInstance()->getIdentifier('app:config.router', array());
            $router_name = sprintf('%sApiRoute', $this->getName());
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
        //import application plugins
        FactoryPlugin::getInstance()->assign('api', $this);

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
     * Run Api
     */
    public function run()
    {
        $output = $this->Router->run();

        $supress_response_codes = (isset($_GET['supress_response_codes'])) ? true : false ;

        if ($supress_response_codes) {
            $code = 200;
        } else {
            $code = isset($output['status']) ? $output['status'] : 200 ;
            unset($output['status']);
        }

        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        $text = (!isset($output['header_message'])) ? 'OK' : $output['header_message'] ;
        header("Access-Control-Allow-Origin: *");
        header($protocol . ' ' . $code . ' ' . $text);

        $callback = isset($_GET['callback']) ? Filter::getInstance()->filter('cyan_callback_func', $_GET['callback']) : '' ;

        if (!empty($callback)) {
            $template = $callback.'(%s)';
        } else {
            $template = '%s';
        }

        echo sprintf($template,json_encode($output));
    }

    /**
     * Return Error Array from app:config.errors
     *
     * @param $code
     * @return array
     */
    public function error($code)
    {
        $errors = Finder::getInstance()->getIdentifier('app:config.errors', array());
        //assign error code to response
        if (isset($errors[$code])) {
            $errors[$code]['code'] = $code;
        }
        return isset($errors[$code]) ? array('error' => $errors[$code]) : array() ;
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