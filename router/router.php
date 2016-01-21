<?php
namespace Cyan\Library;

/**
 * Class Router
 * @package Cyan\Library
 * @since 1.0.0
 */
class Router
{
    use TraitError;

    /**
     * Router constants
     * @since 1.0.0
     */
    const NOT_FOUND = 0, FOUND = 1, METHOD_NOT_ALLOWED = 2, ERROR = 3;

    /**
     * @var array
     * @since 1.0.0
     */
    private $routes = [];

    /**
     * @var array
     * @since 1.0.0
     */
    private $links = [];

    /**
     * Map tokens, defaults, prefixPath, prefixName
     *
     * @var array
     * @since 1.0.0
     */
    private $map = [];

    /**
     * Default route
     *
     * @var string
     * @since 1.0.0
     */
    private $default_route = [
        'uri' => '',
        'parameters' => []
    ];

    /**
     * @var bool
     * @since 1.0.0
     */
    private $sef = false;

    /**
     * Enable SEF
     *
     * @param bool $sef
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setSef($sef = false)
    {
        $this->sef = $sef;

        return $this;
    }

    /**
     * Return REQUESTED_METHOD
     *
     * @return string
     */
    public function getRequestedMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Check if request is ajax
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isAjaxRequest()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;
    }

    /**
     * Check if access from secure server
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isSecure()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? true : false ;
    }

    /**
     * add a link
     *
     * @param string $route_name
     * @param string $url
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setLink($route_name, $url)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (is_null($scheme) || empty($scheme)) {
            throw new RouterRouteException(sprintf(__FUNCTION__.' Invalid URL: %s',$url));
        } elseif (isset($this->routes[$route_name])) {
            throw new RouterRouteException(sprintf('%s is already defined in route.',$route_name));
        }

        $this->links[$route_name] = $url;

        return $this;
    }

    /**
     * set default route
     *
     * @param string $route_name
     * @param array $parameters
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setDefaultRoute($route_name, array $parameters = [])
    {
        $this->default_route = [
            'uri' => $route_name,
            'parameters' => $parameters
        ];

        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setRoutePathPrefix($path)
    {
        $this->map['pathPrefix'] = !isset($this->map['pathPrefix']) ? $path : $this->map['pathPrefix'].'/'.$path ;

        return $this;
    }

    /**
     * @return array
     *
     * @since 1.0.0
     */
    public function getRoutePathPrefix()
    {
        return isset($this->map['pathPrefix']) ? $this->map['pathPrefix'] : [];
    }

    /**
     * Remove one level from pathPrefix until be empty
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function decreaseRoutePathPrefix()
    {
        if (!empty($this->map['pathPrefix'])) {
            $path = explode('/',$this->map['pathPrefix']);
            if (count($path) == 1) {
                unset($this->map['pathPrefix']);
            } else {
                array_pop($path);
                $this->map['pathPrefix'] = implode('/',$path);
            }
        }

        return $this;
    }

    /**
     * reset map
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function reset()
    {
        $this->map = [];

        return $this;
    }

    /**
     * get map parameter
     *
     * @param $key
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getMap($key)
    {
        return isset($this->map[$key]) ? $this->map[$key] : [] ;
    }

    /**
     * @param $name_prefix
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setRouteNamePrefix($name_prefix)
    {
        $this->map['namePrefix'] = $name_prefix;

        return $this;
    }

    /**
     * @param array $tokens
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setRouteTokens(array $tokens)
    {
        $this->map['tokens'] = $tokens;

        return $this;
    }

    /**
     * @param array $defaults
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setRouteDefaults(array $defaults)
    {
        $this->map['defaults'] = $defaults;

        return $this;
    }

    /**
     * Add a route
     *
     * @param string|array $request_methods
     * @param string $route_name
     * @param string $route_uri
     * @param array $route_config
     *
     * @return RouterRoute
     *
     * @since 1.0.0
     */
    public function route($request_methods, $route_name, $route_uri, array $route_config = [])
    {
        if (isset($this->links[$route_name])) {
            throw new RouterRouteException(sprintf('%s is already defined in route.',$route_name));
        }

        $route = (new RouterRoute);
        if (!empty($this->map)) {
            foreach ($this->map as $method => $value) {
                $route->$method($value);
            }
        }
        $route->uri($route_uri)->name($route_name)->via($request_methods);

        // assign config array method => value
        foreach ($route_config as $route_method => $route_method_value) {
            if (!method_exists($route, $route_method)) {
                throw new RouterRouteException(sprintf('$route_config[%s] is not a method from %s', $route_method, get_class($route)));
            }

            if ($route_method == 'handler') {
                $route->handler($route_method_value);
            } elseif (is_string($route_method_value)) {
                $route->$route_method($route_method_value);
            } elseif (is_array($route_method_value)) {
                call_user_func_array([$route, $route_method], [$route_method_value]);
            } else {
                throw new RouterRouteException(sprintf('$route_config[%s] value must be String|Array instead of "%s"', $route_method, gettype($route_method_value)));
            }
        }

        $this->routes[$route->name] = $route;

        return $route;
    }

    /**
     * Create a Route via GET
     *
     * @param string $route_uri
     * @param string $route_name
     * @param mixed $handle
     *
     * @return RouterRoute
     */
    public function get($route_uri, $route_name, $handle)
    {
        return $this->route('get',$route_name, $route_uri)->handler($handle);
    }

    /**
     * Create a Route via POST
     *
     * @param string $route_uri
     * @param string $route_name
     * @param mixed $handle
     *
     * @return RouterRoute
     */
    public function post($route_uri, $route_name, $handle)
    {
        return $this->route('post',$route_name, $route_uri)->handler($handle);
    }

    /**
     * Create a Route via DELETE
     *
     * @param string $route_uri
     * @param string $route_name
     * @param mixed $handle
     *
     * @return RouterRoute
     */
    public function delete($route_uri, $route_name, $handle)
    {
        return $this->route('delete',$route_name, $route_uri)->handler($handle);
    }

    /**
     * Create a Route via PUT
     *
     * @param string $route_uri
     * @param string $route_name
     * @param mixed $handle
     *
     * @return RouterRoute
     */
    public function put($route_uri, $route_name, $handle)
    {
        return $this->route('put',$route_name, $route_uri)->handler($handle);
    }

    /**
     * Dispatch a route
     *
     * @param string $http_method
     * @param array $uri
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function dispatch($http_method, $uri)
    {
        $http_method = strtolower($http_method);
        $response = [];

        // fix path with execution script
        $uri = $this->getPathUri($uri);

        if (empty($uri)) {
            if (empty($this->default_route['uri'])) {
                $response = [self::NOT_FOUND];
            } else {
                $route = $this->routes[$this->default_route['uri']];
                if ($route->match($uri)) {
                    $response = [self::FOUND, $route->handler, $route->handler_arguments];
                } else {
                    $this->redirect($this->generate($this->default_route['uri']));
                }
            }
        } else {
            if (empty($this->routes)) {
                $response = [self::NOT_FOUND];
            } else {
                /** @var RouterRoute $route */
                foreach ($this->routes as $route) {
                    if ($route->match($uri) && empty($response)) {
                        if (!in_array($http_method,$route->request_methods)) {
                            $response = [self::METHOD_NOT_ALLOWED, $route->request_methods];
                        } else {
                            if ($route->secure && !$this->isSecure()) {
                                $redirect_link = str_replace('http','https',$this->getRequestedURL());
                                $this->redirect($redirect_link);
                            }

                            $response = [self::FOUND, $route->handler, $route->handler_arguments, $route];
                        }
                        break;
                    }
                }
            }
        }

        if ($this->hasErrors()) {
            $response = [self::ERROR];
        }

        if (empty($response)) {
            $response = [self::NOT_FOUND];
        }

        return $response;
    }

    /**
     * get execution path from uri
     *
     * @param $uri
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getPathUri($uri)
    {
        $parse_url = parse_url($uri);
        $path = array_diff(explode('/', $parse_url['path']),explode('/', $_SERVER['SCRIPT_NAME']));
        return implode('/', $path );
    }

    /**
     * Full requested url
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getRequestedURL()
    {
        $page_url = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$page_url .= "s";}
        $page_url .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }

        return $page_url;
    }

    /**
     * Dispatch from request url
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function dispatchFromRequest()
    {
        return $this->dispatch($this->getRequestedMethod(), $this->getRequestedURL());
    }

    /**
     * Redirect to uri
     *
     * @param $uri
     *
     * @since 1.0.0
     */
    public function redirect($uri)
    {
        header('location: '.$uri);
        die();
    }

    /**
     * @param $route_name
     */
    public function generate($route_name, array $tokens = [])
    {
        if (!isset($this->routes[$route_name]) && !isset($this->links[$route_name])) {
            throw new RouterException(sprintf('Route "%s" not found', $route_name));
        } else if (isset($this->links[$route_name])) {
            return $this->links[$route_name];
        }

        /** @var RouterRoute $route */
        $route = $this->routes[$route_name];

        $route_prefix = !$this->sef ? $this->getBaseUrl() : '' ;
        $route_sufix = $route->generate($tokens);

        return $route_prefix.$route_sufix;
    }

    /**
     * Get Base Url
     *
     * @param bool $strip_script_file
     * @return string
     *
     * @since 1.0.0
     */
    public function getBaseUrl($strip_script_file = false)
    {
        $script_name = basename($_SERVER['SCRIPT_NAME']);
        $parse_url = parse_url($this->getRequestedURL());
        $path = array_filter(array_intersect(explode('/', $parse_url['path']),explode('/', $_SERVER['SCRIPT_NAME'])));
        if ($strip_script_file && in_array($script_name,$path)) {
            $script_key = array_search($script_name,$path);
            unset($path[$script_key]);
        } elseif (!in_array($script_name,$path)) {
            $path[] = basename($_SERVER['SCRIPT_NAME']);
        }
        return sprintf('%s://%s/%s/', $parse_url['scheme'],$parse_url['host'], implode('/',$path));
    }

    /**
     * Return number of routes
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function countRoutes()
    {
        return count($this->routes);
    }

    /**
     * Return array config
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function toArray()
    {
        $config = [
            'map' => $this->map
        ];

        /** @var RouterRoute $route */
        foreach ($this->routes as $route) {
            $config['routes'][$route->getConfigUri(true)] =  $route->toArray($this);
        }

        return $config;
    }
}