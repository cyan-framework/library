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
    use TraitsSingleton, TraitsEvent, TraitsContainer;

    /**
     * @var \Array
     */
    protected $_route = [];

    /**
     * @var \Array
     */
    protected $_route_name = [];

    /**
     * Special routes start with a variable.
     * Examples: cyan_slug:user, cyan_slug:user/dashboard
     *
     * @var array
     */
    protected $_special_route = [];

    /**
     * Array Request Uri
     *
     * @var array
     */
    protected $path = [];

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
    public function __construct(array $config = [], \Closure $closure = null)
    {
        if (!empty($closure) && is_callable($closure)) {
            $this->__initialize = $closure;
            $this->__initialize();
        }

        $this->_route = $config;

        $uri = parse_url($_SERVER['REQUEST_URI']);

        $requestURI = explode('/', $uri['path']);
        $scriptName = explode('/', $_SERVER['SCRIPT_NAME']);
        $baseUri = [];
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

        //import application plugins
        FactoryPlugin::getInstance()->assign('router', $this);

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
     * @param $route_name
     * @return $this
     */
    protected function mapRequestMethod($method, $uri, $data, $route_name = null)
    {
        $parts = explode('/', $uri);
        if (!empty($parts)) {
            if (strpos($parts[0],':') !== false) {
                if (!isset($this->_special_route[$method])) {
                    $this->_special_route[$method] = [];
                }

                $this->_special_route[$method][$uri] = $data;
            } else {
                if (!isset($this->_route[$method])) {
                    $this->_route[$method] = [];
                }

                $this->_route[$method][$uri] = $data;
            }
        } else {
            if (!isset($this->_route[$method])) {
                $this->_route[$method] = [];
            }

            $this->_route[$method][$uri] = $data;
        }

        if (!is_null($route_name)) {
            $this->_route_name[$route_name] = [
                'url' => $uri,
                'config' => $data
            ];
        }

        return $this;
    }

    /**
     * @param $uri
     * @param $config
     * @return $this
     */
    public function get($uri, $config, $route_name = null)
    {
        if (is_null($route_name) && !isset($config['route_name'])) {
            throw new RouterException('Router config must have a "route_name"');
        } else if (is_null($route_name) && !empty($config['route_name'])) {
            $route_name = $config['route_name'];
            unset($config['route_name']);
        }

        return $this->mapRequestMethod('get', $uri, $config, $route_name);
    }

    /**
     * @param $uri
     * @param $config
     * @return $this
     */
    public function post($uri, $config, $route_name = null)
    {
        if (is_null($route_name) && !empty($config['route_name'])) {
            $route_name = $config['route_name'];
            unset($config['route_name']);
        }

        return $this->mapRequestMethod('post', $uri, $config, $route_name);
    }

    /**
     * @param $uri
     * @param $config
     * @param $route_name
     * @return $this
     */
    public function put($uri, $config, $route_name = null)
    {
        if (is_null($route_name) && !empty($config['route_name'])) {
            $route_name = $config['route_name'];
            unset($config['route_name']);
        }

        return $this->mapRequestMethod('put', $uri, $config, $route_name);
    }

    /**
     * @param $uri
     * @param $config
     * @return $this
     */
    public function delete($uri, $config, $route_name = null)
    {
        if (is_null($route_name) && !empty($config['route_name'])) {
            $route_name = $config['route_name'];
            unset($config['route_name']);
        }

        return $this->mapRequestMethod('delete', $uri, $config, $route_name);
    }

    /**
     * Set all methods GET, POST. PUT, DELETE
     *
     * @param $uri
     * @param $config
     */
    public function rest($uri, $config, $route_name)
    {
        $this->get($uri, $config, $route_name);
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
        $name = ucfirst(strtolower($name));
        $uri = isset($config['path']) ? $config['path'] : $name ;
        $config['action'] = isset($config['action']) ? $config['action'] : '' ;

        $routeConfig = [
            'controller' => sprintf('%sController',$uri)
        ];
        if (!empty($config['action'])) {
            $routeConfig['action'] = $config['action'];
        }
        $this->get($uri, $routeConfig, $this->_createRouteName($uri, $routeConfig));

        return $this;
    }

    /**
     * Create a router name
     *
     * @param $uri
     * @param $config
     * @return string
     */
    private function _createRouteName($uri, $config)
    {
        $name = '';

        if (isset($config['action'])) $name .= '_action';

        return $name;
    }

    /**
     * Create a controller and assign to appication
     *
     * @param $name
     * @param null $config
     * @param callable $closure
     * @return $this
     */
    public function resource($name, $config = null, \Closure $closure = null)
    {
        $name = ucfirst(strtolower($name));
        $controller_name = sprintf('%sController',$name);
        $view_name = sprintf('%sView',$name);

        $app = $this->getContainer('application');
        if (!empty($app)) {
            if (!($app instanceof Application)) {
                throw new RouterException('Current application instance its not an application object');
            }
            $app->Controller->create($controller_name, [], function() use($app, $name, $view_name) {
                $this->get = function() use ($app, $name, $view_name){
                    return $app->View->create($view_name, [
                        'tpl' => strtolower($name)
                    ]);
                };
            });
        } else {
            FactoryController::getInstance()->create($controller_name);
        }
        if (!is_null($config) || !is_null($closure)) {
            if (is_null($closure)) {
                if (is_array($config)) {
                    $uri = strtolower($name);
                    foreach ($config as $filter => $value) {
                        $uri .= '/'.$filter.':'.$value;
                    }
                    $this->get($uri, [
                        'controller' => $controller_name
                    ], $this->_createRouteName($uri,[]));
                } elseif (is_callable($config)) {

                } else {
                    throw new RouterException(sprintf('2nd argument $config must be Array or Closure. Current type "%s".',gettype($config)));
                }
            } elseif (is_callable($closure)) {

            } else {
                throw new RouterException(sprintf('3rd argument $closure must be Closure. Current type "%s".',gettype($closure)));
            }
        } else {
            $uri = strtolower($name);
            $this->get($uri, [
                'controller' => $controller_name
            ], $this->_createRouteName($uri,[]));
        }

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
     * @param callable $closure
     * @return $this
     */
    public function extend(\Closure $closure = null)
    {
        if (!empty($closure) && is_callable($closure)) {
            $this->_extend = $closure->bindTo($this, $this);
            call_user_func($this->_extend);
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
        $route = [];
        $parts = array_filter(explode('/',$uri));

        $special_pos = strpos($uri,'/*');
        $isSpecial = $special_pos === false ? false : true ;

        $diff = array_diff($parts, $this->path);
        $validate_uri = array_diff($parts, $diff);

        if ($special_pos) {
            if (empty($validate_uri)) {
                return $route;
            }
        } else if (count($parts) != count($this->path) && !$isSpecial) {
            return $route;
        }

        $arguments = [];
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
            } elseif (isset($this->path[$key]) && $pattern == $this->path[$key]) {
                if (count($this->path) -1 == $key) {
                    if (is_array($config)) {
                        $route = array_merge($route, $config);
                    } else {
                        unset($parts[$key]);
                    }
                } else {
                    unset($parts[$key]);
                }
            } else {
                return $route;
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
     * Create a router
     *
     * @param $name
     * @param $arguments
     * @param $base
     */
    public function generate($name, array $arguments = [], $base = true)
    {
        if (!isset($this->_route_name[$name])) {
            throw new RouterException(sprintf('Router named "%s" not exists!', $name));
        }
        $match = $this->_route_name[$name];
        $match['config'] = is_array($match['config']) ? array_merge($match['config'],$arguments) : $arguments ;

        if (isset($match['config']['controller']))
            unset($match['config']['controller']);
        if (isset($match['config']['action']))
            unset($match['config']['action']);

        $base_url = $base ? $this->base : implode('/',$this->path) ;

        $route = [];
        $parts = array_filter(explode('/',$match['url']));

        $special_pos = strpos($match['url'],'/*');
        $isSpecial = $special_pos === false ? false : true ;

        $arguments = [];
        $total_arguments = count($parts) - 1;
        foreach ($parts as $key => $pattern) {
            if (preg_match('/[a-z0-9-_A-Z]*:[a-z0-9-_A-Z]*/', $pattern)) {
                $argument = explode(':', $pattern);

                $type = $argument[0];
                $name = $argument[1];
                $value = isset($match['config'][$name]) ? $match['config'][$name] : null ;
                if ($argument[0] == '*') {
                    $route_any = true;
                    $route[$name] = $value;
                } else {
                    $match['url'] = str_replace($pattern, Filter::getInstance()->filter($type,$value), $match['url']);
                    unset($match['config'][$name]);
                }
            }
        }

        $uri = $match['url'];
        if (!empty($match['config']))
            $uri .= '?' . http_build_query($match['config']);

        $app_config = Finder::getInstance()->getIdentifier('app:config.application');
        if (!empty($app_config) && isset($app_config['sef']) && $app_config['sef']) {
            if (isset($app_config['sef_rewrite'])) {
                $file = basename($this->base);
                if (strpos($file,'.php') === false) {
                    return $base_url . '/' . $uri;
                } else {
                    return str_replace($file,'',$this->base) . '/' . $uri;
                }
            }
        } elseif (strpos($this->base,'.php') === false) {
            return $base_url . '/' . basename($_SERVER['SCRIPT_NAME']) . '/' . $uri;
        }

        if ($uri == '/') {
            $uri = '';
        }

        return $base_url . '/' . $uri;
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
    public function run(array $config = [])
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
            if (isset($this->_route[$requested_method])) {
                foreach ($this->_route[$requested_method] as $uri => $uriConfig) {
                    $config = $this->matchUri($uri, $uriConfig);
                    if (isset($config['action']) || isset($config['controller'])) break;
                }
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
            throw new RouterException(sprintf('The URI "%s" did not match any routes.',$this->current));
        }

        if (isset($config['controller']) && is_string($config['controller']) && !empty($config['controller'])) {
            $controller = $config['controller'];
            unset($config['controller']);
        }

        $action = $config['action'];
        unset($config['action']);

        if (isset($config['action']) && is_string($config['action']) && !empty($config['controller'])) {
            if (strpos($action,'.') !== false) {
                $parts = explode('.', $action);
                $controller = $parts[0];
                $action = $parts[1];
            }
        }

        $sufix = !empty($action) ? $action : 'Index' ;
        $action = isset($this->$action) && is_callable($this->$action) ? $action : $requested_method.'Action'.ucfirst($sufix) ;

        $return = '';

        $app = $this->getContainer('application');

        if (!empty($controller)) {
            $class_name = $controller;
            if (strpos($class_name,'controller') === false) {
                $class_name .= 'Controller';
            }
            if (class_exists($class_name, false)) {
                $object = new $class_name($controller);
            } else if ($app instanceof Application) {
                $object = $app->Controller->get($controller);
            } else {
                $object = FactoryController::getInstance()->get($controller);
            }

            if (!is_object($object)) {
                throw new RouterException(sprintf('%s not found',$controller));
            }

            $return = $object->run(array_merge(['action' => $action], $config));
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

            return call_user_func_array([$object, $method], $args[0]);
        } else {
            throw new \BadMethodCallException(sprintf('Undefined "%s" in %s',$name,get_class($this)));
        }
    }

    /**
     * Build URI
     *
     * @param $uri
     * @param $data
     * @return string
     * @throws RouterException
     */
    public function buildUri($uri, $data)
    {
        $requested_method = strtolower($_SERVER['REQUEST_METHOD']);
        if (empty($this->_route)) {
            throw new RouterException('You must configure a router before run an application');
        }

        if (!isset($this->_route[$requested_method]) && !isset($this->_special_route[$requested_method])) {
            throw new RouterException('Invalid Requested method');
        }

        if (!empty($data)) {
            $uri .= '/'.implode('/',array_values($data));
        };

        return $uri;
    }

    /**
     * Redirect to uri
     *
     * @param $uri
     */
    public function redirect($uri)
    {
        header('location: '.$uri);
        die();
    }
}