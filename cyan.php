<?php
// import CyanException class
require_once 'exception.php';
// import autoload
require_once 'autoload' . DIRECTORY_SEPARATOR . 'autoload.php';
// register autoload
\Cyan\Library\Autoload::getInstance([
    'namespaces' => [
        'Cyan\\Library' => __DIR__
    ]
]);

\Cyan\Library\Finder::getInstance()->registerResource('cyan', __DIR__);

/**
 * Class Cyan
 *
 * @property \Cyan\Library\Application $Application
 * @property \Cyan\Library\Autoload $Autoload
 * @property \Cyan\Library\Cache $Cache
 * @property \Cyan\Library\Csrf $CSRF
 * @property \Cyan\Library\Data $Data
 * @property \Cyan\Library\Database $Database
 * @property \Cyan\Library\Filter $Filter
 * @property \Cyan\Library\Finder $Finder
 * @property \Cyan\Library\Form $Form
 * @property \Cyan\Library\Text $Text
 * @property \Cyan\Library\Router $Router
 * @property \Cyan\Library\Session $Session
 * @property \Cyan\Library\Extension $Extension
 *
 * @since 1.0.0
 */
class Cyan
{
    use \Cyan\Library\TraitPrototype, \Cyan\Library\TraitContainer {
        __set as setPrototype;
    }

    /**
     * The framework version
     *
     * @var string
     * @since 1.0.0
     */
    const VERSION = '1.0.0';

    /**
     * The list of helpers currently loaded
     *
     * @var array
     * @since 1.0.0
     */
    protected $helpers = [];

    /**
     * List of alias to helpers
     *
     * @var array
     * @since 1.0.0
     */
    protected $alias = [];

    /**
     * Singleton Instance
     *
     * @var self
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Singleton Instance
     *
     * @param array $config
     *
     * @return Cyan
     *
     * @since 1.0.0
     */
    public static function initialize(array $config = []) {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Cyan constructor.
     *
     * @param array $config
     *
     * @since 1.0.0
     */
    final public function __construct(array $config = [])
    {
        define('_CYAN_EXEC', true);

        $this->addHelper(\Cyan\Library\Autoload::getInstance());
        $this->addHelper(\Cyan\Library\Finder::getInstance());
    }

    /**
     * Add a helper
     *
     * @param $helper
     * @param string $alias
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function addHelper($helper, $alias = '')
    {
        if (!is_object($helper)) {
            throw new CyanException(sprintf('Cyan->addHelper not support %s "%s" type.', get_class($helper), gettype($helper)));
        }
        $reflection = new ReflectionClass($helper);
        $this->helpers[$reflection->getShortName()] = $helper;

        // add alias
        if (!empty($alias)) {
            $this->alias[$alias] = $helper;
        }
        unset($reflection);

        return $this;
    }

    /**
     * Register a alias to a helper class
     *
     * @param string $alias
     * @param string $helper
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setAlias($alias, $helper)
    {
        $this->alias[$alias] = $helper;

        return $this;
    }

    /**
     * Return Helper
     *
     * @param $name
     *
     * @return mixed
     *
     * @throws CyanException
     *
     * @since 1.0.0
     */
    public function getHelper($name)
    {
        if (isset($this->alias[$name])) {
            $name = $this->alias[$name];
        }

        if (!isset($this->helpers[$name])) {
            $class = strtolower($name);
            $file_path = $this->Finder->getPath(sprintf('cyan:%s.%s',$class,$class),'.php');
            if (!file_exists($file_path)) {
                throw new CyanException(sprintf('Cyan\\%s not found in %s',$name, $file_path));
            }
            $class_name = sprintf('\Cyan\Library\%s', $name);
            $reflection = new ReflectionClass($class_name);
            if (in_array('TraitSingleton',$reflection->getTraitNames())) {
                $this->helpers[$name] = $class_name::getInstance();
            } else {
                $this->helpers[$name] = $reflection->newInstance();
            }
        }

        return $this->helpers[$name];
    }

    /**
     * List of registered helpers
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getHelpers()
    {
        return array_keys($this->helpers);
    }

    /**
     * List of registered aliases
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getAliases()
    {
        return array_keys($this->alias);
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
            throw new CyanException(sprintf('Cyan\\%s already defined',$name));
        }

        return $this->setPrototype($name, $value);
    }
}