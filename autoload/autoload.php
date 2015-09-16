<?php
namespace Cyan\Library;

/**
 * Class Autoload
 * @package Cyan\Library
 */
class Autoload
{
    /**
     * Namespace/directory pairs to search
     *
     * @var array
     */
    protected $_namespaces = [];

    /**
     * @var array
     */
    protected $_prefixes = [];

    /**
     * @var array
     */
    protected $_classes = [];

    /**
     * File name
     *
     * @var String
     */
    protected $_file = '';

    /**
     * Singleton Instance
     *
     * @var self
     */
    private static $instance;

    /**
     * Singleton Instance
     *
     * @param array $config
     * @return Singleton
     */
    public static function getInstance(array $config = []) {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Autoload
     *
     * @param $config
     */
    public function __construct($config)
    {
        if(isset($config['namespaces']))
        {
            $this->registerNamespaces($config['namespaces']);
        }

        if(isset($config['prefixes']))
        {
            $this->registerPrefixes($config['prefixes']);
        }

        //Register the loader with the PHP autoloader
        $this->register();
    }

    /**
     * Registers the loader with the PHP autoloader.
     *
     * @param Boolean $prepend Whether to prepend the autoloader or not
     * @see \spl_autoload_register();
     */
    public function register($prepend = false)
    {
        spl_autoload_register([$this, 'loadClass'], true, $prepend);
    }

    /**
     * Unregisters the loader with the PHP autoloader.
     *
     * @see \spl_autoload_unregister();
     */
    public function unregister()
    {
        spl_autoload_unregister([$this, 'loadClass']);
    }

    /**
     * Register a namespace
     *
     * @param string|array $namespaces
     * @param string|array $paths The location(s) of the namespace
     * @return ClassLocatorInterface
     */
    public function registerNamespace($namespace, $paths)
    {
        $namespace = trim($namespace, '\\');
        $this->_namespaces['\\'.$namespace] = (array) $paths;

        krsort($this->_namespaces, SORT_STRING);

        return $this;
    }

    /**
     * Registers an array of namespaces
     *
     * @param array $namespaces An array of namespaces (namespaces as keys and locations as values)
     * @return ClassLocatorInterface
     */
    public function registerNamespaces(array $namespaces)
    {
        foreach ($namespaces as $namespace => $paths)
        {
            $namespace = trim($namespace, '\\');
            $this->_namespaces['\\'.$namespace] = (array) $paths;
        }

        krsort($this->_namespaces, SORT_STRING);

        return $this;
    }

    /**
     * register prefix to autoload
     *
     * @param $prefix
     * @param $path
     * @return $this
     */
    public function registerPrefix($prefix, $path)
    {
        $prefix = trim($prefix);
        $path = trim($path);

        $this->_prefixes[$prefix] = $path;

        krsort($this->_prefixes, SORT_STRING);

        return $this;
    }


    public function registerPrefixes(array $prefixes)
    {
        foreach ($prefixes as $prefix => $paths)
        {
            $prefix = trim($prefix);
            $this->_prefixes[$prefix] = (array) $paths;
        }

        krsort($this->_prefixes, SORT_STRING);

        return $this;
    }

    /**
     * register prefix to autoload
     *
     * @param $prefix
     * @param $path
     * @return $this
     */
    public function registerClass($class, $path)
    {
        $class = trim($class);
        $path = trim($path);

        $this->_classes[$class] = $path;

        krsort($this->_classes, SORT_STRING);

        return $this;
    }


    public function registerClasses(array $classes)
    {
        foreach ($classes as $class => $path)
        {
            $this->registerClass($class, $path);
        }

        krsort($this->_classes, SORT_STRING);

        return $this;
    }

    /**
     * Autoload a camelCase class
     *
     * @param $class_name
     */
    public function loadClass($class)
    {
        $result = true;

        if (!$this->isDeclared($class)) {
            //Get the path
            $path = $this->findPath( $class );
            if ($path !== false) {
                if (substr($path,-4) == '.php') {
                    $result = $this->loadFile($path);
                } else {
                    $tmp = preg_replace('/([a-z0-9])([A-Z])/', "$1 $2",$this->_file);
                    if (str_word_count($tmp) == 1) {
                        $tmp .= DIRECTORY_SEPARATOR . $tmp;
                    } else {
                        $parts = explode(' ',str_replace('\\',' ',$tmp));
                        $parts[] = end($parts);
                        $tmp = implode(DIRECTORY_SEPARATOR,$parts);
                    }
                    $file_path = $path . DIRECTORY_SEPARATOR . strtolower($tmp) . '.php';
                    $result = $this->loadFile($file_path);
                }
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Get the path based on a class name
     *
     * @param string $class The class name
     * @return string|false Returns canonicalized absolute pathname or FALSE of the class could not be found.
     */
    public function findPath($class)
    {
        $class_path = false;

        if (strpos($class,"\\") === false) {
            $class_path = $this->findClassPath($class, $this->_prefixes);
            if (!$class_path) {
                $class_path = $this->findClassPath($class, $this->_classes);
            }
        } else {
            $class_path = $this->findClassPath($class, $this->_namespaces);
        }

        if (is_array($class_path) && count($class_path) == 1) {
            $class_path = end($class_path);
        }

        return $class_path;
    }

    /**
     * Return class path by array check
     *
     * @param $class
     * @param $data
     * @return bool
     */
    private function findClassPath($class, $data)
    {
        $this->_file = null;
        $class_path = false;
        $keys = array_keys($data);

        foreach ($keys as $key) {
            $keyTrim = ltrim($key,"\\");
            if (strpos($class,$key) === false && strpos($class,$keyTrim) === false) continue;
            $this->_file = substr($class,strlen($key));
            $class_path = $data[$key];
            break;
        }

        return $class_path;
    }

    /**
     * Load a class based on a path
     *
     * @param string $path The file path
     * @return boolean Returns TRUE if the file could be loaded, otherwise returns FALSE.
     */
    public function loadFile($path)
    {
        $path = str_replace("\\",DIRECTORY_SEPARATOR, $path);
        //Don't re-include files and stat the file if it exists.
        if (!in_array($path, get_included_files()) && is_readable($path)) {
            require $path;
        }

        return true;
    }

    /**
     * Tells if a class, interface or trait exists.
     *
     * @params string $class
     * @return boolean
     */
    public function isDeclared($class)
    {
        return class_exists($class, false)
        || interface_exists($class, false)
        || trait_exists($class, false);
    }
}