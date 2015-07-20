<?php

/**
 * Class Cyan
 *
 * @var \Cyan\Library\Finder $Finder
 * @var \Cyan\Library\FactoryApi $Api
 * @var \Cyan\Library\FactoryApplication $Application
 * @var \Cyan\Library\FactoryRouter $Router
 * @var \Cyan\Library\FactoryView $View
 * @var \Cyan\Library\Data $Data
 * @var \Cyan\Library\FactoryController $Controller
 * @var \Cyan\Library\FactoryPlugin $Plugin
 * @var \Cyan\Library\FactoryDatabase $Database
 * @var \Cyan\Library\Filter $Filter
 * @var \Cyan\Library\Csrf $CSRF
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
     * @var Cyan\Library\Autoload
     */
    public $Loader;

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

        //Create loader
        require_once $this->_path . '/autoload/autoload.php';

        $config_autoloader = [
            'namespaces' => [
                '\Cyan\Library' => __DIR__
            ]
        ];
        $loader = \Cyan\Library\Autoload::getInstance($config_autoloader);

        \Cyan\Library\Filter::getInstance()->mapFilters([
            'cyan_int' => '/[0-9]*/',
            'cyan_float' => '/^[0-9]*\.?[0-9]+$/',
            'cyan_string' => '/[a-zA-ZãÃáÁàÀêÊéÉèÈíÍìÌôÔõÕóÓòÒúÚùÙûÛçÇ\s\-]*/',
            'cyan_word' => '/[a-zA-ZãÃáÁàÀêÊéÉèÈíÍìÌôÔõÕóÓòÒúÚùÙûÛçÇ_\-]*/',
            'cyan_search' => '/[A-ZA-za-z0-9\.\s]*/',
            'cyan_username' => '/^[A-ZA-za-z0-9_.]*$/',
            'cyan_password' => '/^[A-ZA-za-z0-9_.]*$/',
            'cyan_email' => '/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/',
            'cyan_action' => '/([A-ZA-za-z]*|[A-ZA-za-z]*.[A-ZA-za-z]*)/',
            'cyan_slug' => '/[0-9A-ZA-za-z-_]*/',
            'cyan_uri' => '/[0-9A-ZA-za-z-_\/]*/',
            'cyan_url' => '/\b(?:(?:https?):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i',
            'cyan_rest_methods', '/(post|put|delete|get)/',
            'cyan_rest_fields' => '/^[A-Za-z][A-Za-z0-9](?:[.,A-Za-z0-9]+)$/',
            'cyan_callback_func' => '/^([A-ZA-za-z0-9_.])*$/',
            'cyan_base64' => '/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?$/'
        ]);

        //Assign Factories
        $this->Finder = \Cyan\Library\Finder::getInstance();
        $this->Loader = $loader;

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
    public function setRootPath($root_path)
    {
        if (!is_dir($root_path)) {
            throw new \RuntimeException(sprintf('You must set a directory path in $cyan->setRootPath(), "%s" given.',gettype($root_path)));
        }

        $this->_rootPath = $root_path;

        return $this;
    }

    /**
     * Create instance according with docblock class var
     *
     * @param $key
     * @return null
     */
    public function __get($key)
    {
        if (!isset($this->$key)) {
            $rc = new \ReflectionClass($this);
            $result = [];
            preg_match_all('/@(\w+)\s+(.*)\r?\n/m', $rc->getDocComment(), $matches);

            $doc_block = [];
            foreach ($matches[1] as $index => $value) {
                $doc_block[$value][] = $matches[2][$index];
            }

            foreach ($doc_block['var'] as $var) {
                list($class, $variable) = explode(' ',$var);
                $variable = substr($variable,1);
                if ($variable === $key) {
                    $this->$key = $class::getInstance();
                }
            }
        }

        return isset($this->$key) ? $this->$key : null ;
    }

    /**
     * Return Root Directory Path
     */
    public function getRootPath()
    {
        return $this->_rootPath;
    }
}