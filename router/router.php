<?php
namespace Cyan\Library;

/**
 * Class Router
 * @package Cyan\Library\Router
 */
class Router
{
    /**
     * Traits
     */
    use TraitsSingleton, TraitsEvent;

    /**
     * @var \Array
     */
    protected $_route = array();

    /**
     * Special routes start with a variable.
     * Examples: cyan_slug:user, cyan_slug:user/dashboard
     *
     * @var array
     */
    protected $_special_route = array();

    /**
     * Array Request Uri
     *
     * @var array
     */
    protected $path = array();

    /**
     * Full request uri
     *
     * @var string
     */
    public $base = '';

    /**
     * String request uri
     *
     * @var string
     */
    public $current = '';

    /**
     * @var string
     */
    protected $_default_uri = '';

    /**
     * @param array $config
     * @param callable $closure
     */
    public function __construct(array $config = array(), \Closure $closure = null)
    {
        if (!empty($closure) && is_callable($closure)) {
            $this->__initialize = $closure;
            $this->__initialize();
        }

        $this->_route = $config;

        $uri = parse_url($_SERVER['REQUEST_URI']);

        $requestURI = explode('/', $uri['path']);
        $scriptName = explode('/', $_SERVER['SCRIPT_NAME']);
        $baseUri = array();
        for($i= 0;$i < sizeof($scriptName);$i++)
        {
            if ($requestURI[$i] == $scriptName[$i])
            {
                $baseUri[] = $requestURI[$i];
                unset($requestURI[$i]);
            }
        }

        $baseUri = array_filter($baseUri);
        $protocol = $this->isSecure() ? "https://" : "http://";
        $this->path = array_values(array_filter($requestURI));
        $this->base = empty($baseUri) ? $protocol.$_SERVER['HTTP_HOST'] : $protocol.$_SERVER['HTTP_HOST'].'/'.implode('/',array_values($baseUri));
        $this->current = implode('/', $this->path);

        $this->trigger('Initialize', $this);
    }

    /**
     * @return array
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param array $path
     */
    public function setPath(array $path)
    {
        $this->path = $path;
    }

    /**
     * Get Number of defined routes
     *
     * @return int
     */
    public function countRoutes()
    {
        return count($this->_route) + count($this->_special_route);
    }

    /**
     * Define Error Handle
     *
     * @param callable $closure
     */
    public function setError(\Closure $closure)
    {
        $this->error = $closure;
    }

    /**
     * @return bool
     */
    public function isSecure()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? true : false ;
    }

    /**
     * @param $method
     * @param $uri
     * @param $data
     * @return $this
     */
    protected function mapRequestMethod($method, $uri, $data)
    {
        $uri = $uri;

        $parts = explode('/', $uri);
        if (!empty($parts)) {
            if (strpos($parts[0],':') !== false) {
                if (!isset($this->_special_route[$method])) {
                    $this->_special_route[$method] = array();
                }

                $this->_special_route[$method][$uri] = $data;
            } else {
                if (!isset($this->_route[$method])) {
                    $this->_route[$method] = array();
                }

                $this->_route[$method][$uri] = $data;
            }
        } else {
            if (!isset($this->_route[$method])) {
                $this->_route[$method] = array();
            }

            $this->_route[$method][$uri] = $data;
        }

        return $this;
    }

    /**
     * @param $uri
     * @param $config
     * @return $this
     */
    public function get($uri, $config)
    {
        return $this->mapRequestMethod('get', $uri, $config);
    }

    /**
     * @param $uri
     * @param $config
     * @return $this
     */
    public function post($uri, $config)
    {
        return $this->mapRequestMethod('post', $uri, $config);
    }

    /**
     * @param $uri
     * @param $config
     * @return $this
     */
    public function put($uri, $config)
    {
        return $this->mapRequestMethod('put', $uri, $config);
    }

    /**
     * @param $uri
     * @param $config
     * @return $this
     */
    public function delete($uri, $config)
    {
        return $this->mapRequestMethod('delete', $uri, $config);
    }

    /**
     * Set all methods GET, POST. PUT, DELETE
     *
     * @param $uri
     * @param $config
     */
    public function rest($uri, $config)
    {
        $this->get($uri, $config);
        $this->post($uri, $config);
        $this->put($uri, $config);
        $this->delete($uri, $config);

        return $this;
    }

    /**
     * Multiple Methods Configuration
     *
     * @param array $methods
     * @param $uri
     * @param $config
     * @return $this
     * @throws RouterException
     */
    public function match(array $methods, $uri, $config)
    {
        foreach ($methods as $method) {
            if (!method_exists($this, $method)) {
                throw new RouterException(sprintf('Method "%s" is not supported by router.',$method));
            }
            $this->$method($uri, $config);
        }

        return $this;
    }

    /**
     * Create a Router for a Controller
     *
     * @param $name
     * @param $config
     * @return $this
     */
    public function route($name, $config)
    {
        $uri = isset($config['path']) ? $config['path'] : $name ;
        $config['action'] = isset($config['action']) ? $config['action'] : '' ;

        $routeConfig = array(
            'controller' => sprintf('%sController',$uri)
        );
        if (!empty($config['action'])) {
            $routeConfig['action'] = $config['action'];
        }
        $this->get($uri, $routeConfig);

        return $this;
    }

    /**
     * @param callable $closure
     * @return $this
     */
    public function map(\Closure $closure = null)
    {
        if (!empty($closure) && is_callable($closure)) {
            $this->_closure = $closure->bindTo($this, $this);
            call_user_func($this->_closure);
        }

        return $this;
    }

    /**
     * @param $uri
     * @param $config
     * @return array
     */
    private function matchUri($uri, $config)
    {
        $route_any = false;
        $route = array();
        $parts = array_filter(explode('/',$uri));

        $isSpecial = strpos($uri,'/*') === false ? false : true ;

        if (count($this->path) != count($parts) && !$isSpecial) {
            return $route;
        }

        $arguments = array();
        $total_arguments = count($parts);
        foreach ($parts as $key => $pattern) {
            if (preg_match('/[a-z0-9-_A-Z]*:[a-z0-9-_A-Z]*/', $pattern)) {
                $argument = explode(':', $pattern);

                $type = $argument[0];
                $name = $argument[1];
                $value = $this->path[$key];
                if ($argument[0] == '*') {
                    $route_any = true;
                    $route[$name] = $value;
                } else {
                    $route[$name] = Filter::getInstance()->filter($type,$value);
                }

                if (count($this->path) -1 == $key && is_array($config)) {
                    $route = array_merge($route, $config);
                }
            }
            elseif (isset($this->path[$key]) && $pattern == $this->path[$key]) {
                if (count($this->path) -1 == $key) {
                    if (is_array($config)) {
                        $route = array_merge($route, $config);
                    } else {
                        unset($parts[$key]);
                    }
                } else {
                    unset($parts[$key]);
                }
            }
        }

        if (is_callable($config)) {
            if (count($route) == count($parts) || $isSpecial) {
                $generate_alias = !empty($this->current) ? $this->current : uniqid() ;
                $this->$generate_alias = $config;
                $route['action'] = $generate_alias;
                return $route;
            }
        }

        if (empty($parts) && !empty($config) && is_array($config)) {
            $route = $config;
        }

        return $route;
    }

    /**
     * @param $uri
     * @return $this
     */
    public function setDefault($uri)
    {
        $this->_default_uri = (string)$uri;

        return $this;
    }

    /**
     * Run dispatcher
     *
     * @param array $config
     * @throws \RuntimeException
     */
    public function run(array $config = array())
    {
        $this->trigger('BeforeRun', $this);

        if (empty($config)) {
            $requested_method = strtolower($_SERVER['REQUEST_METHOD']);
            if (empty($this->_route)) {
                throw new RouterException('You must configure a router before run an application');
            }

            if (!isset($this->_route[$requested_method]) && !isset($this->_special_route[$requested_method])) {
                throw new RouterException('Invalid Requested method');
            }

            // set default if empty
            if (empty($this->path) && !empty($this->_default_uri)) {
                $this->path = array($this->_default_uri);
            }

            //search by router
            foreach ($this->_route[$requested_method] as $uri => $uriConfig) {
                $config = $this->matchUri($uri, $uriConfig);
                if (isset($config['action']) || isset($config['controller'])) break;
            }

            //search especial uri
            if (isset($this->_special_route[$requested_method]) && empty($config)) {
                foreach ($this->_special_route[$requested_method] as $uri => $uriConfig) {
                    $config = $this->matchUri($uri, $uriConfig);
                    if (isset($config['action']) || isset($config['controller'])) break;
                }
            }
        }

        if (empty($config)) {
            if (isset($this->error)) {
                if (is_callable($this->error)) {
                    return call_user_func($this->error);
                } else {
                    return $this->error;
                }
            }
            throw new RouterException('Please send a config to dispatcher');
        }

        $controller = isset($config['controller']) && is_string($config['controller']) ? $config['controller'] : null ;
        $action = isset($config['action']) && is_string($config['action']) ? $config['action'] : null ;
        if (isset($config['controller'])) unset($config['controller']);
        unset($config['action']);

        if (strpos($action,'.') !== false) {
            $parts = explode('.', $action);
            $controller = $parts[0];
            $action = $parts[1];
        }

        $action = isset($this->$action) && is_callable($this->$action) ? $action : $requested_method.$action ;

        $return = '';

        if (isset($controller) && !empty($controller)) {
            if (!empty(\Cyan::initialize()->Application->current)) {
                $object = \Cyan::initialize()->Application->current->Controller->getController($controller);
            } else {
                $object = FactoryController::getInstance()->getController($controller);
            }

            $return = $object->run(array_merge(array('action' => $action), $config));
        } else {
            $return = $this->$action($config);
        }

        $this->trigger('AfterRun', $this);

        return $return;
    }

    /**
     * Call Closure
     *
     * @param $name
     * @param $args
     * @return mixed
     */
    public function __call($name, $args) {
        if (isset($this->$name) && is_callable($this->$name)) {
            return call_user_func_array($this->$name, (is_array($args) && !empty($args)) ? $args[0] : $args);
        } else if (isset($this->$name) && is_object($this->$name)) {
            if (!is_string($args[0])) {
                throw new \BadMethodCallException(sprintf('Undefined method to request "%s"',get_class($this->$name)));
            }
            $object = $this->$name;
            $method = array_shift($args);

            if (!method_exists($object, $method)) {
                throw new \BadMethodCallException(sprintf('Undefined method "%s" in "%s"',$method,get_class($this->$name)));
            }

            return call_user_func_array(array($object, $method), $args[0]);
        } else {
            throw new \BadMethodCallException(sprintf('Undefined "%s" in %s',$name,get_class($this)));
        }
    }
}