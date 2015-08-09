<?php
namespace Cyan\Library;


/**
 * Class CSRF
 *
 * @package Cyan\Library
 */
class Csrf
{
    /**
     * Self Instance
     *
     * @var self
     */
    protected static $instance;

    /**
     * Singleton instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Create a INPUT
     *
     * @return string
     */
    public function getInput(array $customAttributes = [])
    {
        $defaultAttributes = [
            'type' => 'hidden',
            'name' => $this->getTokenID(),
            'value' => $this->getToken()
        ];
        $fieldAttributes = array_merge($customAttributes,$defaultAttributes);
        $html = '<input';
        foreach ($fieldAttributes as $attr => $value) {
            $html .= sprintf(' %s="%s"', $attr, $value);
        }
        $html .= '/>';
        return $html;
    }

    /**
     * @return mixed
     */
    public function getTokenID()
    {
        if (!isset($_SESSION['token_id'])) {
            $token_id = $this->random(10);
            $_SESSION['token_id'] = $token_id;
        }

        return $_SESSION['token_id'];
    }

    /**
     * @return string
     */
    public function getToken()
    {
        if (!isset($_SESSION['token_value'])) {
            $token = hash('sha256', $this->random(500));
            $_SESSION['token_value'] = $token;
        }

        return $_SESSION['token_value'];
    }

    /**
     * Validate CSRF
     *
     * @param $method
     * @return bool
     */
    public function isValid($method = null)
    {
        $method = is_null($method) ? strtolower($_SERVER['REQUEST_METHOD'])  : strtolower($method) ;
        switch ($method) {
            case 'get':
                $handle = $_GET;
                break;
            case 'post':
                $handle = $_POST;
                break;
        }

        if(isset($handle) && isset($handle[$this->getTokenID()]) && ($handle[$this->getTokenID()] == $this->getToken())) {
            unset($_SESSION['token_value']);
            unset($_SESSION['token_id']);
            return true;
        }

        unset($_SESSION['token_value']);
        unset($_SESSION['token_id']);
        return false;
    }

    /**
     * @param $len
     * @return string
     */
    private function random($len)
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $byteLen = intval(($len / 2) + 1);
            $return = substr(bin2hex(openssl_random_pseudo_bytes($byteLen)), 0, $len);
        } elseif (@is_readable('/dev/urandom')) {
            $f=fopen('/dev/urandom', 'r');
            $urandom=fread($f, $len);
            fclose($f);
            $return = '';
        }

        if (empty($return)) {
            for ($i=0;$i<$len;++$i) {
                if (!isset($urandom)) {
                    if ($i%2==0) {
                        mt_srand(time()%2147 * 1000000 + (double)microtime() * 1000000);
                    }
                    $rand=48+mt_rand()%64;
                } else {
                    $rand=48+ord($urandom[$i])%64;
                }

                if ($rand>57)
                    $rand+=7;
                if ($rand>90)
                    $rand+=6;

                if ($rand==123) $rand=52;
                if ($rand==124) $rand=53;
                $return.=chr($rand);
            }
        }

        return $return;
    }
}