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
     * @var FactoryDatabase
     */
    public $Database = false;

    /**
     * @var Text
     */
    public $Text = false;

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


        if (!isset($this->_data['build_index'])) {
            $this->_data['build_index'] = true;
        }

        if (isset($initialize) && is_callable($initialize)) {
            $this->__initializer = $initialize->bindTo($this, $this);
            $this->__initializer();
        }

        $this->advanceReadiness();
    }

    /**
     * Default Application
     */
    public function initialize()
    {
        if ($this->Controller == false) {
            $this->Controller = new FactoryController;
        }

        if ($this->View == false) {
            $this->View = new FactoryView;
        }

        if ($this->Database == false) {
            $db_configs = Finder::getInstance()->getIdentifier('app:config.database', []);
            $db_factory = FactoryDatabase::getInstance();
            foreach ($db_configs as $db_name => $db_config) {
                $db_factory->create($db_name, $db_config);
            }
            $this->Database = $db_factory;
        }

        if ($this->Router == false) {
            $router_config = Finder::getInstance()->getIdentifier('app:config.router', []);
            $router_name = sprintf('%sApplicationRoute', $this->getName());
            $router_factory = FactoryRouter::getInstance();
            $this->Router = $router_factory->get($router_name, $router_factory->create($router_name, $router_config));
        }

        $filters = Finder::getInstance()->getIdentifier('app:config.filters', []);
        if (!empty($filters)) {
            Filter::getInstance()->mapFilters($filters);
        }

        if ($this->Text == false) {
            $language = !empty($this->getConfig()['language']) ? $this->getConfig()['language'] : '' ;
            $this->Text = Text::getInstance();
            if (!empty($language)) {
                $this->Text->loadLanguage($language);
            }
        }

        if ($this->_data['build_index']) {
            $this->Router->resource('index');
            $this->Router->setDefault('index');
        }

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
        return Finder::getInstance()->getIdentifier('app:config.application', []);
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
            $theme_config = Finder::getInstance()->getIdentifier('app:config.theme', $this->getConfig());
            $this->Theme = new Theme($theme_config);
        }

        if ($this->Text == false) {
            $this->Text = new Text;
            $config = $this->getConfig();
            if (isset($config['language']))
                $this->Text->loadLanguage($config['language']);
        }

        $this->trigger('BeforeRun', $this);

        $response = $this->Router->run();

        $this->trigger('AfterRun', $this);

        $this->Theme->set('outlet', $response);

        echo $this->Theme;
    }

    /**
     * Create a Controller if not exists
     *
     * @param $name
     */
    public function __get($name)
    {
        if (strpos($name,'Controller')) {
            $controller_name = str_replace('Controller','',$name);
            if (!$this->Controller->exists($controller_name)) {
                $this->Controller->create($controller_name);
            }
            return $this->Controller->get($controller_name);
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