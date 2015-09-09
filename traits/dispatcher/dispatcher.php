<?php
namespace Cyan\Library;

/**
 * Class TraitsDispatcher
 * @package Cyan\Library
 */
trait TraitsDispatcher
{
    /**
     * Traits
     */
    use TraitsPrototype, TraitsEvent;

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
            throw new TraitsException('Please send a config to dispatcher');
        }

        $errors = [];

        $controller = isset($config['controller']) && is_string($config['controller']) ? $config['controller'] : null ;
        $action = isset($config['action']) && is_string($config['action']) ? $config['action'] : null ;
        if (isset($config['controller'])) unset($config['controller']);
        unset($config['action']);

        if (is_null($action)) {
            $errors[] = 'Missing argument "action" to config';
        }

        if (strpos($action,'.') !== false) {
            $parts = explode('.', $action);
            $controller = $parts[0];
            $action = $parts[1];
        }

        if (count($errors)) {
            throw new TraitsException(implode($errors));
        }

        if (is_string($controller)) {
            if (!property_exists($this,$controller)) {
                throw new TraitsException(sprintf('Undefined "%s" in %s',$controller,get_class($this)));
            }

            $return = $this->$controller($action, $config);
        } else {
            if (!property_exists($this,$action) && !method_exists($this,$action)) {
                throw new TraitsException(sprintf('Undefined "%s" in %s',$action,get_class($this)));
            }

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
            if (!is_array($args) && !empty($args)) {
                $arguments = [$args];
            } elseif (count($args) == 1) {
                $arguments = $args[0];
            } else {
                $arguments = array_values($args);
            }
            return call_user_func_array($this->$name, $arguments);
        } else if (isset($this->$name) && is_object($this->$name)) {
            if (!is_string($args[0])) {
                throw new TraitsException(sprintf('Undefined method to request "%s"',get_class($this->$name)));
            }
            $object = $this->$name;
            $method = array_shift($args);

            if (!method_exists($object, $method)) {
                throw new TraitsException(sprintf('Undefined method "%s" in "%s"',$method,get_class($this->$name)));
            }

            return call_user_func_array([$object, $method], $args[0]);
        } elseif (!class_exists($name, false)) {
            $object = new $name;
            $method = array_shift($args);
            return call_user_func_array([$object, $method], $args[0]);
        }
    }
}