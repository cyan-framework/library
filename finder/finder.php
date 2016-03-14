<?php
namespace Cyan\Library;

/**
 * Class Finder
 * @package Cyan\Library\Finder
 * @since 1.0.0
 *
 * @method Finder getInstance
 */
class Finder
{
    use TraitSingleton;

    /**
     * Cache array
     *
     * @var array
     * @since 1.0.0
     */
    protected $cache = [];

    /**
     * List of resources paths
     *
     * @var array
     * @since 1.0.0
     */
    protected $resources = [];

    /**
     * List of callbacks
     *
     * @var array
     * @since 1.0.0
     */
    protected $callbacks = [];

    /**
     * Alias to resources
     *
     * @var array
     * @since 1.0.0
     */
    protected $alias = [];

    /**
     * Register a resource
     *
     * @param $name
     * @param $path
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function registerResource($name, $path)
    {
        $name = strtolower($name);
        $this->resources[$name] = $path;

        return $this;
    }

    /**
     * Resource list
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getResources()
    {
        return array_keys($this->resources);
    }

    /**
     * Single Resource
     *
     * @param $name
     *
     * @return null
     *
     * @since 1.0.0
     */
    public function getResource($name)
    {
        $name = strtolower($name);
        return isset($this->resources[$name]) ? $this->resources[$name] : null ;
    }

    /**
     * @param $alias
     * @param $identifier
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function registerAlias($alias, $identifier)
    {
        $this->alias[$alias] = $identifier;
        return $this;
    }

    /**
     * List of aliases
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
     * Single Alias
     *
     * @param $alias
     *
     * @return null
     *
     * @since 1.0.0
     */
    public function getAlias($alias)
    {
        return isset($this->alias[$alias]) ? $this->alias[$alias] : null ;
    }

    /**
     * @param $identifier
     *
     * @return string
     *
     * @since 1.0.0
     * @throws FinderException
     */
    public function getPath($identifier, $ext = '')
    {
        $parse = parse_url($identifier);

        if (!isset($parse['scheme'])) {
            throw new FinderException(sprintf('invalid string identifier: %s', $identifier));
        }

        if (!isset($this->resources[$parse['scheme']])) {
            throw new FinderException(sprintf('"%s" are not registered as an resource.',$parse['scheme']));
        }

        return implode(DIRECTORY_SEPARATOR,array_merge([$this->resources[$parse['scheme']]], empty($parse['path']) ? [] : explode('.',$parse['path']))).$ext;
    }

    /**
     * Check if resource exists
     *
     * @param $name
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function hasResource($name)
    {
        return isset($this->resources[$name]);
    }

    /**
     * Register a callback for specific identifier
     *
     * @param $callback
     *
     * @since 1.0.0
     */
    public function registerCallback(\Closure $callback)
    {
        $this->callbacks[] = $callback;
    }

    /**
     * Return a Object
     *
     * @example type:path.name
     *
     * @param $identifier
     *
     * @since 1.0.0
     */
    public function getIdentifier($identifier, $default = null)
    {
        if (isset($_cache[$identifier])) {
            return $_cache[$identifier];
        }
        $parse = parse_url($identifier);

        if (!isset($this->resources[$parse['scheme']]) && !isset($this->alias[$parse['scheme']])) {
            if (!isset($this->resources[$parse['scheme']])) {
                return $default;
            }
        }

        if (isset($this->alias[$parse['scheme']])) {
            $base_path = $this->getPath($this->alias[$parse['scheme']]);
        } else {
            $base_path = $this->resources[$parse['scheme']];
        }

        $file_path = $base_path . DIRECTORY_SEPARATOR . str_replace('.',DIRECTORY_SEPARATOR,$parse['path']);
        $file_path = strtolower($file_path) . '.php';

        if (!file_exists($file_path)) {
            return $default;
        }

        $Cyan = \Cyan::initialize();
        $return = require $file_path;

        $class_name = ucfirst($parse['scheme']) . implode(array_map('ucfirst', explode('.',$parse['path'])));

        // no cache for string response
        if (is_string($return)) {
            return $return;
        } elseif (($return instanceof \stdClass) || is_callable($return) || is_array($return)) {
            $config = Config::getInstance($identifier);
            if (is_array($return)) {
                $config->loadArray($return);
                $return = $config;
            } elseif (($return instanceof \stdClass)) {
                $config->loadObject($return);
                $return = $config;
            }
            $this->cache[$identifier] = $return;
        } else {
            foreach ($this->callbacks as $callback) {
                if ((bool)$callback($identifier, $this, $return, $parse['path'], $class_name)) {
                    return $return;
                }
            }

            if (!class_exists($class_name,false)) {
                throw new FinderException(sprintf('Undefined "%s" class in path "%s".',$class_name,$file_path));
            }

            $instance = new $class_name;
            $this->cache[$identifier] = $instance;
        }

        if (isset($this->cache[$identifier])) {
            return $this->cache[$identifier];
        }

        throw new FinderException(sprintf('Identifier "%s" not found',$identifier));
    }

    /**
     * set cache to a identifier
     *
     * @param string $identifier
     * @param mixed $value
     * @param bool $override
     *
     * @return mixed
     *
     * @since 1.0.0
     * @throws FinderException
     */
    public function setCache($identifier, $value, $override = false)
    {
        if (!$override && isset($this->cache[$identifier])) {
            throw new FinderException(sprintf('Finder::$cache "%s" is already defined!', $identifier));
        }
        $this->cache[$identifier] = $value;
    }
}