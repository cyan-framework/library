<?php
/**
 * Class Cyan
 */
class Cyan
{
    /**
     * Library Version
     *
     * @var string
     */
    const VERSION = '0.1';

    /**
     * Library Path
     *
     * @var string
     */
    protected $_path;

    /**
     * @var
     */
    protected $_rootPath;

    /**
     * Singleton Instance
     *
     * @var self
     */
    private static $instance;

    /**
     * Closure initialize
     *
     * @var Closure
     */
    protected $__initialize;

    /**
     * @var \Cyan\Library\FactoryApplication
     */
    public $Application;

    /**
     * @var Cyan\Library\FactoryRouter
     */
    public $Router;

    /**
     * @var \Cyan\Library\FactoryView
     */
    public $View;

    /**
     * @var \Cyan\Library\Data
     */
    public $Data;

    /**
     * @var \Cyan\Library\FactoryController
     */
    public $Controller;

    /**
     * @var Cyan\Library\FactoryPlugin
     */
    public $Plugin;

    /**
     * @var \Cyan\Library\FactoryConnection
     */
    public $Connection;

    /**
     * @var Cyan\Library\Finder
     */
    public $Finder;

    /**
     * Initialize Library
     *
     * @param bool $auto_register_apps True if you want to auto register apps on initialize framework
     */
    final public function __construct(array $config = array())
    {
        //Initialize the path
        $this->_path = __DIR__;

        $script_path = dirname($_SERVER['SCRIPT_FILENAME']);

        $this->_rootPath = ($script_path !== $this->_path) ? $script_path : dirname($this->_path) ;
        if (isset($config['path']) && is_dir($config['path'])) {
            $this->_rootPath = $config['path'];
        }

        if (!isset($config['autoregister_apps'])) {
            $config['autoregister_apps'] = true;
        }

        //Create loader
        require_once $this->_path . '/autoload/autoload.php';

        $config_autoloader = array(
            'namespaces' => array(
                '\Cyan\Library' => __DIR__
            )
        );
        $loader = \Cyan\Library\Autoload::getInstance($config_autoloader);

        \Cyan\Library\Filter::getInstance()->mapFilters(array(
            'cyan_int' => '/[0-9]*/',
            'cyan_str' => '/[A-ZA-za-z]*/',
            'cyan_action' => '/([A-ZA-za-z]*|[A-ZA-za-z]*.[A-ZA-za-z]*)/',
            'cyan_slug' => '/[0-9A-ZA-za-z-_]*/',
            'cyan_rest_methods', '/(post|put|delete|get)/',
            'cyan_rest_fields' => '/^[A-Za-z][A-Za-z0-9](?:[.,A-Za-z0-9]+)$/',
            'cyan_callback_func' => '/^([A-ZA-za-z0-9_.])*$/'
        ));

        //Assign Factories
        $this->Connection = \Cyan\Library\FactoryConnection::getInstance();
        $this->Router = \Cyan\Library\FactoryRouter::getInstance();
        $this->Application = \Cyan\Library\FactoryApplication::getInstance();
        $this->View = \Cyan\Library\FactoryView::getInstance();
        $this->Controller = \Cyan\Library\FactoryController::getInstance();
        $this->Plugin = \Cyan\Library\FactoryPlugin::getInstance();
        $this->Finder = \Cyan\Library\Finder::getInstance();
        $this->Data = \Cyan\Library\Data::getInstance();

        //register root application path as resource
        $this->Finder->registerResource('root', $this->_rootPath);

        //auto assign apps under root
        if ($config['autoregister_apps']) {
            $app_path = $this->_rootPath . DIRECTORY_SEPARATOR . 'app';
            $app_paths = glob($app_path.'/*', GLOB_ONLYDIR);
            foreach ($app_paths as $path) {
                if (file_exists($path . DIRECTORY_SEPARATOR . 'application.php')){
                    $prefix = basename($path);
                    $this->Finder->registerResource($prefix, $path);
                    //register Autoload based on app folder
                    $prefix = ucfirst($prefix);
                    $loader->registerPrefix($prefix, $path);
                }
            }
        }
    }

    /**
     * Singleton Instance
     *
     * @param array $config
     * @return Cyan
     */
    public static function initialize() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Prevent clone of this class
     */
    final private function __clone() { }

    /**
     * Get Library Version
     *
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Get Library Path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Define Root Path
     *
     * @param $root_path
     * @return $this
     * @throws RuntimeException
     */
    public function setRootPath($root_path)
    {
        if (!is_dir($root_path)) {
            throw new RuntimeException(sprintf('You must set a directory path in $cyan->setRootPath(), "%s" given.',gettype($root_path)));
        }

        $this->_rootPath = $root_path;

        return $this;
    }

    /**
     * Return Root Directory Path
     */
    public function getRootPath()
    {
        return $this->_rootPath;
    }
}