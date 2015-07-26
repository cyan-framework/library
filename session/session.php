<?php
namespace Cyan\Library;

class Session
{
    use TraitsSingleton;

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
}