<?php
namespace Cyan\Framework;

/**
 * Class Autoload
 * @package Cyan\Framework
 * @since 1.0.0
 */
class Autoload
{
    /**
     * Namespace/directory pairs to search
     *
     * @var array
     * @since 1.0.0
     */
    protected $namespaces = [];

    /**
     * @var array
     * @since 1.0.0
     */
    protected $prefixes = [];

    /**
     * @var array
     * @since 1.0.0
     */
    protected $classes = [];

    /**
     * File name
     *
     * @var String
     * @since 1.0.0
     */
    protected $file = '';

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
     * @return Autoload
     *
     * @since 1.0.0
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
     *
     * @since 1.0.0
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
     *
     * @see \spl_autoload_register();
     *
     * @since 1.0.0
     */
    public function register($prepend = false)
    {
        spl_autoload_register([$this, 'loadClass'], true, $prepend);
    }

    /**
     * Unregisters the loader with the PHP autoloader.
     *
     * @see \spl_autoload_unregister();
     *
     * @since 1.0.0
     */
    public function unregister()
    {
        spl_autoload_unregister([$this, 'loadClass']);
    }

    /**
     * Register a namespace
     *
     * @param string $namespace
     * @param string|array $paths The location(s) of the namespace
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function registerNamespace($namespace, $paths)
    {
        $namespace = trim($namespace, '\\');
        $this->namespaces['\\'.$namespace] = (array) $paths;

        krsort($this->namespaces, SORT_STRING);

        return $this;
    }

    /**
     * Register a alias for a class
     *
     * @param string $class_name
     * @param string $class_alias
     * @param bool $autoload
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function registerClassAlias($class_name, $class_alias, $autoload = true)
    {
        class_alias($class_name, $class_alias, $autoload);

        return $this;
    }

    /**
     * Registers an array of namespaces
     *
     * @param array $namespaces An array of namespaces (namespaces as keys and locations as values)
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function registerNamespaces(array $namespaces)
    {
        foreach ($namespaces as $namespace => $paths)
        {
            $namespace = trim($namespace, '\\');
            $this->namespaces['\\'.$namespace] = (array) $paths;
        }

        krsort($this->namespaces, SORT_STRING);

        return $this;
    }

    /**
     * register prefix to autoload
     *
     * @param $prefix
     * @param $path
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function registerPrefix($prefix, $path)
    {
        $prefix = trim($prefix);

        $this->prefixes[$prefix] = (array) $path;

        krsort($this->prefixes, SORT_STRING);

        return $this;
    }

    /**
     * Register Array of prefixes
     *
     * @param array $prefixes
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function registerPrefixes(array $prefixes)
    {
        foreach ($prefixes as $prefix => $paths)
        {
            $prefix = trim($prefix);
            $this->prefixes[$prefix] = (array) $paths;
        }

        krsort($this->prefixes, SORT_STRING);

        return $this;
    }

    /**
     * Register a single Class
     *
     * @param $class_name
     * @param $path
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function registerClass($class_name, $path)
    {
        $class_name = trim($class_name);
        $path = trim($path);

        $this->classes[$class_name] = $path;

        krsort($this->classes, SORT_STRING);

        return $this;
    }

    /**
     * Register a list of classes
     *
     * @param array $classes
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function registerClasses(array $classes)
    {
        foreach ($classes as $class_name => $path)
        {
            $this->classes[$class_name] = $path;
        }

        krsort($this->classes, SORT_STRING);

        return $this;
    }

    /**
     * Autoload a camelCase class
     *
     * @param $class_name
     *
     * @since 1.0.0
     */
    public function loadClass($class_name)
    {
        $result = true;

        if (!$this->isDeclared($class_name)) {
            //Get the path
            $path = $this->findPath( $class_name );
            if ($path !== false) {
                if (substr($path,-4) == '.php') {
                    $result = $this->loadFile($path);
                } else {
                    $tmp = preg_replace('/([a-z0-9])([A-Z])/', "$1 $2",$this->file);
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
     *
     * @since 1.0.0
     */
    public function findPath($class)
    {
        $class_path = false;

        if (strpos($class,"\\") === false) {
            $class_path = $this->findClassPath($class, $this->prefixes);
            if (!$class_path) {
                $class_path = $this->findClassPath($class, $this->classes);
            }
        } else {
            $class_path = $this->findClassPath($class, $this->namespaces);
        }

        if (is_array($class_path) && count($class_path) == 1) {
            $class_path = end($class_path);
        }

        return $class_path;
    }

    /**
     * Return class path by array check
     *
     * @param $class_name
     * @param $data
     *
     * @return bool
     *
     * @since 1.0.0
     */
    private function findClassPath($class_name, $data)
    {
        $this->file = null;
        $class_path = false;
        $keys = array_keys($data);

        foreach ($keys as $key) {
            $keyTrim = ltrim($key,"\\");
            if (strpos($class_name,$key) === false && strpos($class_name,$keyTrim) === false) continue;
            $this->file = substr($class_name,strlen($key));
            $class_path = $data[$key];
            break;
        }

        return $class_path;
    }

    /**
     * Load a class based on a path
     *
     * @param string $path The file path
     *
     * @return boolean Returns TRUE if the file could be loaded, otherwise returns FALSE.
     *
     * @since 1.0.0
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
     * @params string $class_name
     *
     * @return boolean
     *
     * @since 1.0.0
     */
    public function isDeclared($class_name)
    {
        return class_exists($class_name, false)
        || interface_exists($class_name, false)
        || trait_exists($class_name, false);
    }
}