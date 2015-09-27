<?php
use Cyan\Library\Controller;
use Cyan\Library\Csrf;
use Cyan\Library\Data;
use Cyan\Library\FactoryApi;
use Cyan\Library\FactoryApplication;
use Cyan\Library\FactoryController;
use Cyan\Library\FactoryDatabase;
use Cyan\Library\FactoryPlugin;
use Cyan\Library\FactoryRouter;
use Cyan\Library\FactoryView;
use Cyan\Library\Filter;
use Cyan\Library\Finder;

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
    const VERSION = '0.5';

    /**
     * Library Path
     *
     * @var string
     */
    protected $_path;

    /**
     * App path
     *
     * @var string
     */
    protected $_appPath;

    /**
     * Root path
     *
     * @var string
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
     * @var Cyan\Library\Autoload
     */
    public $Loader;

    /**
     * @var \Cyan\Library\Finder
     */
    public $Finder;

    /**
     * @var FactoryApi
     */
    public $Api;

    /**
     * @var FactoryApplication
     */
    public $Application;

    /**
     * @var FactoryRouter
     */
    public $Router;

    /**
     * @var FactoryView
     */
    public $View;

    /**
     * @var Data
     */
    public $Data;

    /**
     * @var Controller
     */
    public $Controller;

    /**
     * @var FactoryPlugin
     */
    public $Plugin;

    /**
     * @var FactoryDatabase
     */
    public $Database;

    /**
     * @var Filter
     */
    public $Filter;

    /**
     * @var Csrf
     */
    public $CSRF;

    /**
     * Initialize Library
     *
     * @param bool $auto_register_apps True if you want to auto register apps on initialize framework
     */
    final public function __construct(array $config = [])
    {
        define('_CYAN_EXEC', true);

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

        $this->_appPath = (isset($config['app_path']) && is_dir($config['app_path'])) ? $config['app_path'] : $this->_rootPath ;

        //Create loader
        require_once $this->_path . '/autoload/autoload.php';

        $configAutoloader = [
            'namespaces' => [
                '\Cyan\Library' => __DIR__
            ]
        ];
        $loader = \Cyan\Library\Autoload::getInstance($configAutoloader);

        \Cyan\Library\Filter::getInstance()->mapFilters([
            'cyan_int' => '/[0-9]*/',
            'cyan_float' => '/^[0-9]*\.?[0-9]+$/',
            'cyan_dbhost' => '/^([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])(\.([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]{0,61}[a-zA-Z0-9]))*$/',
            'cyan_string' => '/[a-zA-ZãÃáÁàÀêÊéÉèÈíÍìÌôÔõÕóÓòÒúÚùÙûÛçÇ\s\-0-9]*/',
            'cyan_word' => '/[a-zA-ZãÃáÁàÀêÊéÉèÈíÍìÌôÔõÕóÓòÒúÚùÙûÛçÇ_\-]*/',
            'cyan_alphanum' => '/^[0-9a-z]*$/i',
            'cyan_search' => '/[A-ZA-za-z0-9\.\s]*/i',
            'cyan_username' => '/^[A-ZA-za-z0-9_.]*$/i',
            'cyan_password' => '/^[A-ZA-za-z0-9_.]*$/i',
            'cyan_email' => '/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/',
            'cyan_action' => '/([A-ZA-za-z]*|[A-ZA-za-z]*.[A-ZA-za-z]*)/i',
            'cyan_slug' => '/[0-9A-ZA-za-z-_]*/i',
            'cyan_uri' => '/[0-9A-ZA-za-z-_\/]*/i',
            'cyan_url' => '/\b(?:(?:https?):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i',
            'cyan_rest_methods', '/(post|put|delete|get)/',
            'cyan_rest_fields' => '/^[A-Za-z][A-Za-z0-9](?:[.,A-Za-z0-9]+)$/',
            'cyan_callback_func' => '/^([A-ZA-za-z0-9_.])*$/',
            'cyan_base64' => '/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?$/'
        ]);

        //Assign Factories
        $this->Api = FactoryApi::getInstance();
        $this->Application = FactoryApplication::getInstance();
        $this->Controller = FactoryController::getInstance();
        $this->View = FactoryView::getInstance();
        $this->Router = FactoryRouter::getInstance();
        $this->Plugin = FactoryPlugin::getInstance();
        $this->CSRF = Csrf::getInstance();
        $this->Filter = Filter::getInstance();
        $this->Finder = Finder::getInstance();
        $this->Data = Data::getInstance();
        $this->Loader = $loader;

        //register root application path as resource
        $this->Finder->registerResource('root', $this->_rootPath);
        $this->Finder->registerResource('cyan', $this->_path);

        //auto assign apps under root
        if ($config['autoregister_apps']) {
            $app_path = $this->_appPath . DIRECTORY_SEPARATOR . 'app';
            if (is_dir($app_path) && file_exists($app_path)) {
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
    }

    /**
     * Singleton Instance
     *
     * @param array $config
     * @return Cyan
     */
    public static function initialize(array $config = []) {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self($config);
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
    public function setRootPath($rootPath)
    {
        if (!is_dir($rootPath) && !file_exists($rootPath)) {
            throw new \RuntimeException(sprintf('You must set a directory path in $cyan->setRootPath(), "%s" given.',gettype($rootPath)));
        }

        $this->_rootPath = $rootPath;

        return $this;
    }

    /**
     * Define Root Path
     *
     * @param $root_path
     * @return $this
     * @throws RuntimeException
     */
    public function setAppPath($app_path)
    {
        if (!is_dir($app_path) && !file_exists($app_path)) {
            throw new \RuntimeException(sprintf('You must set a directory path in $cyan->setAppPath(), "%s" given.',gettype($root_path)));
        }

        $this->_appPath = $app_path;

        return $this;
    }

    /**
     * Return Root Directory Path
     */
    public function getRootPath()
    {
        return $this->_rootPath;
    }

    /**
     * Return Root Directory Path
     */
    public function getAppPath()
    {
        return $this->_appPath;
    }
}