<?php
namespace Cyan\Library;

/**
 * Class Finder
 * @package Cyan\Library\Finder
 */
class Finder
{
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
     * @return self
     */
    public static function getInstance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Cache array
     *
     * @var array
     */
    protected $_cache = array();

    /**
     * List of resources paths
     *
     * @var array
     */
    protected $_resources = array();

    /**
     * Alias to resources
     *
     * @var array
     */
    protected $_alias = array();

    /**
     * Register a resource
     *
     * @param $alias
     * @param $path
     * @return $this
     */
    public function registerResource($alias, $path)
    {
        $alias = strtolower($alias);
        $this->_resources[$alias] = $path;

        return $this;
    }

    /**
     * Resource list
     *
     * @return array
     */
    public function getResources()
    {
        return $this->_resources;
    }

    /**
     * Resource Keys list
     *
     * @return array
     */
    public function getResourcesKey()
    {
        return array_keys($this->_resources);
    }

    /**
     * Single Resource
     *
     * @param $alias
     * @return null
     */
    public function getResource($alias)
    {
        return isset($this->_resources[$alias]) ? $this->_resources[$alias] : null ;
    }

    /**
     * @param $alias
     * @param $identifier
     * @return $this
     */
    public function registerAlias($alias, $identifier)
    {
        $this->_alias[$alias] = $identifier;
        return $this;
    }

    /**
     * List of aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return $this->_alias;
    }

    /**
     * Single Alias
     *
     * @param $alias
     * @return null
     */
    public function getAlias($alias)
    {
        return isset($this->_alias[$alias]) ? $this->_alias[$alias] : null ;
    }

    /**
     * @param $identifier
     * @return string
     * @throws FinderException
     */
    public function getPath($identifier, $ext = '')
    {
        $parse = parse_url($identifier);

        if (!isset($this->_resources[$parse['scheme']])) {
            throw new FinderException(sprintf('"%s" are not registered as an resource.',$parse['scheme']));
        }

        return implode(DIRECTORY_SEPARATOR,array_merge(array($this->_resources[$parse['scheme']]), empty($parse['path']) ? array() : explode('.',$parse['path']))).$ext;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasResource($name)
    {
        return isset($this->_resources[$name]);
    }

    /**
     * Return a Object
     *
     * @example type:path.name
     * @param $identifier
     */
    public function getIdentifier($identifier, $default = null)
    {
        if (isset($_cache[$identifier])) {
            return $_cache[$identifier];
        }
        $parse = parse_url($identifier);

        if (!isset($this->_resources[$parse['scheme']]) && !isset($this->_alias[$parse['scheme']])) {
            if (!isset($this->_resources[$parse['scheme']])) {
                return $default;
            }
        }

        if (isset($this->_alias[$parse['scheme']])) {
            $base_path = $this->getPath($this->_alias[$parse['scheme']]);
        } else {
            $base_path = $this->_resources[$parse['scheme']];
        }

        $file_path = $base_path . DIRECTORY_SEPARATOR . str_replace('.',DIRECTORY_SEPARATOR,$parse['path']);
        $file_path = strtolower($file_path) . '.php';

        if (!file_exists($file_path)) {
            return $default;
        }

        $return = require $file_path;

        $class = ucfirst($parse['scheme']) . implode(array_map('ucfirst', explode('.',$parse['path'])));

        // no cache for string response
        if (is_string($return)) {
            return $return;
        } elseif (is_object($return) || is_array($return)) {
            $this->_cache[$identifier] = $return;
        } else {
            if ($parse['path'] == 'application') {
                $this->registerResource('app', $this->_resources[$parse['scheme']]);
                if ($return instanceof Application) {
                    return $return;
                } else {
                    $app = FactoryApplication::getInstance();
                    if ($app->exists($parse['scheme'])) {
                        return $app->get($parse['scheme']);
                    }
                }
            }

            if (!class_exists($class,false)) {
                throw new FinderException(sprintf('Undefined "%s" class in path "%s".',$class,$file_path));
            }

            $instance = new $class;
            $this->_cache[$identifier] = $instance;
        }

        if (isset($this->_cache[$identifier])) {
            return $this->_cache[$identifier];
        }

        throw new FinderException(sprintf('Identifier "%s" not found',$identifier));
    }
}