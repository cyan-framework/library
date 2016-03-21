<?php
namespace Cyan\Framework;

/**
 * Class RouterRoute
 * @package Cyan\Framework
 * @since 1.0.0
 */
class RouterRoute
{
    /**
     * @var string
     * @since 1.0.0
     */
    private $uri;

    /**
     * @var string
     * @since 1.0.0
     */
    private $regex = '/\{(\/)?\w+((\,\w+)+)?\}/i';

    /**
     * @var string
     * @since 1.0.0
     */
    private $name;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $name_prefix;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $path_prefix;

    /**
     * @var array
     * @since 1.0.0
     */
    private $tokens = [];

    /**
     * @var array
     * @since 1.0.0
     */
    private $defaults = [];

    /**
     * @var string
     * @since 1.0.0
     */
    protected $wildcard = null;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $request_methods = [];

    /**
     * Force access by HTTPS
     *
     * @var bool
     * @since 1.0.0
     */
    protected $secure = false;

    /**
     * @var mixed
     * @since 1.0.0
     */
    protected $handler;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $handler_arguments = [];

    /**
     * RouterRoute constructor.
     * @param $uri
     */
    public function __construct($uri = null)
    {
        if (!empty($uri)) {
            $this->uri = $uri;
        }
    }

    /**
     * @param $uri
     * @return $this
     */
    public function uri($uri)
    {
        if ($this->uri !== null) {
            $message = __CLASS__ . '::$uri is immutable once set';
            throw new RouterRouteException($message);
        }
        if ($uri == '/' && !empty($this->path_prefix)) {
            $uri = '';
        }
        $path_prefix = !empty($this->path_prefix) ? $this->path_prefix . '/' : '';
        $this->uri = $path_prefix . $uri;


        return $this;
    }

    /**
     * @param array $tokens
     * @return $this
     */
    public function tokens(array $tokens)
    {
        foreach ($tokens as $key => $regex) {
            $filter = Filter::getInstance();
            if (in_array($regex,$filter->getFiltersList())) {
                $tokens[$key] = $filter->getRegex($regex);
            }
        }
        $this->tokens = array_merge($this->tokens, $tokens);

        return $this;
    }

    /**
     * @param array $defaults
     * @return $this
     */
    public function defaults(array $defaults)
    {
        $this->defaults = array_merge($this->defaults, $defaults);

        return $this;
    }

    /**
     * @param $name
     * @return $this
     * @throws RouteException
     */
    public function name($name)
    {
        if ($this->name !== null) {
            $message = __CLASS__ . '::$name is immutable once set';
            throw new RouterRouteException($message);
        }
        $this->name = $this->name_prefix . $name;

        return $this;
    }

    /**
     * @param $namePrefix
     * @return $this
     * @throws RouteException
     */
    public function namePrefix($name_prefix)
    {
        if ($this->name !== null) {
            $message = __CLASS__ . '::$namePrefix is immutable once $name is set';
            throw new RouterRouteException($message);
        }
        $this->name_prefix = $name_prefix;

        return $this;
    }

    /**
     * @param string|array $request_methods
     * @return $this
     */
    public function via($request_methods)
    {
        $this->request_methods = array_merge($this->request_methods, (array) $request_methods);

        return $this;
    }

    /**
     * @param $path_prefix
     * @return $this
     * @throws RouteException
     */
    public function pathPrefix($path_prefix)
    {
        if ($this->uri !== null) {
            $message = __CLASS__ . '::$uri is immutable once $uri is set';
            throw new RouteException($message);
        }
        $this->path_prefix = $path_prefix;

        return $this;
    }

    /**
     * @param bool $secure
     * @return $this
     */
    public function secure($secure = true)
    {
        $this->secure = ($secure === null) ? null : (bool) $secure;

        return $this;
    }

    /**
     * @param $wildcard
     * @return $this
     */
    public function wildcard($wildcard)
    {
        $this->wildcard = $wildcard;

        return $this;
    }

    /**
     * @param $handler
     * @return $this
     */
    public function handler($handler)
    {
        if ($handler === null) {
            $handler = $this->name;
        }
        $this->handler = $handler;

        return $this;
    }

    /**
     * @param $clean_token
     * @param $token
     * @param $match_uri
     */
    private function uriToken($clean_token, $token, $match_uri, &$vars)
    {
        $regex = $this->tokens[$clean_token];
        preg_match($regex, $match_uri, $matches);
        $vars[$token] = !empty($matches[0]) ? $matches[0] : '' ;
        if (empty($vars[$token]) && isset($this->defaults[$clean_token])) {
            $vars[$token] = $this->defaults[$clean_token];
        }
        $this->handler_arguments[$clean_token] = $vars[$token];
    }

    /**
     * Match a uri
     *
     * @param string $request_uri
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function match($request_uri)
    {
        preg_match_all($this->regex,$this->uri,$uri_tokens);
        $uri_parts = array_filter(explode('/',preg_replace($this->regex,'',$this->uri)));
        $request_uri_parts = explode('/',$request_uri);

        if (isset($this->path_prefix) && count(array_diff(explode('/',$this->path_prefix),$request_uri_parts))) {
            return false;
        } elseif (count(array_diff($uri_parts,$request_uri_parts))) {
            return false;
        }

        if (implode('/', $uri_parts) === $request_uri && empty($uri_tokens[0])) {
            return true;
        }

        $match_uri_parts = array_values(array_diff($request_uri_parts,$uri_parts));
        $match_uri = implode('/',$match_uri_parts);

        if (!empty($uri_tokens[0])) {
            $vars = [];
            foreach ($uri_tokens[0] as $token) {
                $clean_token = substr($token,1,-1);
                if ($clean_token[0] == '/' && strpos($clean_token,',')) {
                    $token_parts = explode(',',substr($clean_token,1));

                    if (empty($match_uri_parts)) {
                        foreach ($token_parts as $token_part) {
                            $this->handler_arguments[$token_part] = null;
                        }
                        return true;
                    } elseif (count($match_uri_parts) > count($token_parts) && empty($this->wildcard)) {
                        return false;
                    }

                    foreach ($token_parts as $token_part) {
                        if (isset($this->tokens[$token_part])) {
                            $this->uriToken($token_part, $token_part, isset($match_uri_parts) && !empty($match_uri_parts) ? array_shift($match_uri_parts) : '', $vars);
                        }
                    }
                } else {
                    $this->uriToken($clean_token, $token, array_shift($match_uri_parts), $vars);
                }
            }

            if (isset($token_parts)) {
                return true;
            } else {
                $match_uri_validate = str_replace(array_keys($vars),array_values($vars),$this->uri);

                if ($request_uri == $match_uri_validate) {
                    return true;
                }
            }
        }

        if (!empty($this->wildcard)) {
            $this->handler_arguments[$this->wildcard] = array_values($match_uri_parts);
            return true;
        }

        return false;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->$key;
    }

    /**
     * @param array $tokens
     * @return string
     */
    public function generate(array $tokens = [])
    {
        preg_match_all($this->regex,$this->uri,$uri_tokens);
        $uri_parts = array_filter(explode('/',preg_replace($this->regex,'',$this->uri)));

        if (!empty($uri_tokens[0])) {
            $replace_tokens = [];
            foreach ($uri_tokens[0] as $uri_token) {
                $clean_token = substr($uri_token,1,-1);
                if ($clean_token[0] == '/' && strpos($clean_token,',')) {
                    $token_parts = explode(',',substr($clean_token,1));
                    $token_replace_parts = [];

                    $verify_tokens = array_intersect_key(array_flip($token_parts),$tokens);
                    if (empty($verify_tokens)) {
                        throw new RouterRouteException(sprintf('Missing route optional token: %s',implode(',',$token_parts)));
                    }
                    $required_tokens = array_slice($token_parts, 0, max($verify_tokens) + 1);
                    foreach ($required_tokens as $token_part) {
                        if (isset($this->defaults[$token_part]) && empty($tokens[$token_part])) {
                            $regex = $this->tokens[$token_part];
                            $value = $tokens[$token_part];
                            preg_match($regex, $value, $matches);
                            if (empty($matches[0])) {
                                throw new RouterRouteException(sprintf('invalid default value "%s" for token %s',$value, $token_part));
                            }
                            $token_replace_parts[] = $matches[0];
                        } elseif (isset($tokens[$token_part])) {
                            $regex = $this->tokens[$token_part];
                            $value = $tokens[$token_part];
                            preg_match($regex, $value, $matches);
                            if (empty($matches[0])) {
                                throw new RouterRouteException(sprintf('invalid value "%s" for token %s',$value, $token_part));
                            }
                            $token_replace_parts[] = $matches[0];
                            unset($tokens[$token_part]);
                        } else {
                            throw new RouterRouteException(sprintf('Missing route token: %s',$token_part));
                        }
                    }

                    $replace_tokens[$uri_token] = '/'.implode('/',$token_replace_parts);
                } else {
                    if (isset($this->defaults[$clean_token]) && empty($tokens[$clean_token])) {
                        $replace_tokens[$uri_token] = $this->defaults[$clean_token];
                        if (isset($tokens[$clean_token])) unset($tokens[$clean_token]);
                    } elseif (isset($tokens[$clean_token])) {
                        $regex = $this->tokens[$clean_token];
                        $value = $tokens[$clean_token];
                        preg_match($regex, $value, $matches);
                        if (empty($matches[0])) {
                            throw new RouterRouteException(sprintf('invalid value "%s" for token %s',$value, $clean_token));
                        }
                        $replace_tokens[$uri_token] = $matches[0];
                        unset($tokens[$clean_token]);
                    } else {
                        throw new RouterRouteException(sprintf('Missing route token: %s',$clean_token));
                    }
                }
            }

            $link = str_replace(array_keys($replace_tokens),array_values($replace_tokens),$this->uri);
        } else {
            $link = implode('/',$uri_parts);
        }

        if (!empty($tokens)) {
            $link .= '?' . http_build_query($tokens);
        }

        return str_replace('//','/',$link);
    }

    /**
     * @param bool $remove_path_prefix
     * @return string
     *
     * @since 1.0.0
     */
    public function getConfigUri($remove_path_prefix = false)
    {
        $uri = $this->uri;
        return $remove_path_prefix ? str_replace($this->path_prefix,'', $uri) : $uri;
    }

    /**
     * Route to Array
     *
     * @since 1.0.0
     */
    public function toArray(Router $router)
    {
        $config = [
            'route_name' => $this->name,
            'via' => $this->request_methods,
            'handler' => $this->handler
        ];

        if (!empty($this->wildcard)) {
            $config['wildcard'] = $this->wildcard;
        }

        $defaults = array_diff($this->defaults,$router->getMap('defaults'));
        if (!empty($defaults)) {
            $config['defaults'] = $defaults;
        }

        $tokens = array_diff($this->tokens,$router->getMap('tokens'));
        if (!empty($tokens)) {
            $config['tokens'] = $tokens;
        }

        return $config;
    }
}