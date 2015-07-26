<?php
namespace Cyan\Library;

/**
 * Class Session
 * @package Cyan\Library
 */
class Session
{
    use TraitsSingleton;

    /**
     * @param array $config
     * @throws SessionException
     */
    public function __construct(array $config)
    {
        if (!isset($config['name'])) {
            throw new SessionException('Invalid Session Configuration');
        }
        if (!isset($config['secure'])) {
            $config['secure'] = false;
        }
        if (!isset($config['httponly'])) {
            $config['httponly'] = true;
        }

        if (ini_set('session.use_only_cookies', 1) === FALSE) {
            die("Could not initiate a safe session (ini_set)");
        }

        // Gets current cookies params.
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $config['secure'], $config['httponly']);
        session_name($config['name']);
        session_start();
        //session_regenerate_id(true);
    }

    /**
     * Get data from the session store
     *
     * @param   string  $name       Name of a variable
     * @param   mixed   $default    Default value of a variable if not set
     * @param   string  $namespace  Namespace to use, default to 'default'
     *
     * @return  mixed  Value of a variable
     *
     * @since   11.1
     */
    public function get($name, $default = null, $namespace = 'default')
    {
        // Add prefix to namespace to avoid collisions
        $namespace = '__' . $namespace;

        if (session_id() == null)
        {
            throw new SessionException('Session has not started');
        }

        if (isset($_SESSION[$namespace][$name]))
        {
            return $_SESSION[$namespace][$name];
        }

        return $default;
    }

    /**
     * Set data into the session store.
     *
     * @param   string  $name       Name of a variable.
     * @param   mixed   $value      Value of a variable.
     * @param   string  $namespace  Namespace to use, default to 'default'.
     *
     * @return  mixed  Old value of a variable.
     *
     * @since   11.1
     */
    public function set($name, $value = null, $namespace = 'default')
    {
        // Add prefix to namespace to avoid collisions
        $namespace = '__' . $namespace;

        if (session_id() == null)
        {
            throw new SessionException('Session has not started');
        }

        $old = isset($_SESSION[$namespace][$name]) ? $_SESSION[$namespace][$name] : null;

        if (null === $value)
        {
            unset($_SESSION[$namespace][$name]);
        }
        else
        {
            $_SESSION[$namespace][$name] = $value;
        }

        return $old;
    }

    /**
     * Check whether data exists in the session store
     *
     * @param   string  $name       Name of variable
     * @param   string  $namespace  Namespace to use, default to 'default'
     *
     * @return  boolean  True if the variable exists
     *
     * @since   11.1
     */
    public function has($name, $namespace = 'default')
    {
        // Add prefix to namespace to avoid collisions.
        $namespace = '__' . $namespace;

        if (session_id() == null)
        {
            throw new SessionException('Session has not started');
        }

        return isset($_SESSION[$namespace][$name]);
    }
}