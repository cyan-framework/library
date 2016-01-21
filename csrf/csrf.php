<?php
namespace Cyan\Library;


/**
 * Class CSRF (Cross-Site Request Forgery)
 *
 * @package Cyan\Library
 * @since 1.0.0
 *
 * @method Csrf getSingleton
 */
class Csrf
{
    use TraitSingleton;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        if (!session_id()) {
            throw new CsrfException('Error: you must start session to use this class.');
        }
    }

    /**
     * Create a INPUT
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getInput(array $custom_attributes = [], $force_new = false)
    {
        $default_attributes = [
            'type' => 'hidden',
            'name' => $this->getTokenID($force_new),
            'value' => $this->getToken($force_new)
        ];
        $field_attributes = array_merge($custom_attributes,$default_attributes);
        $html = '<input';
        foreach ($field_attributes as $attr => $value) {
            $html .= sprintf(' %s="%s"', $attr, $value);
        }
        $html .= '/>';
        return $html;
    }

    /**
     * create token ID
     *
     * @param bool $force_new
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getTokenID($force_new = false)
    {
        if (!isset($_SESSION['cyan.csrf.token_id']) || $force_new) {
            $_SESSION['cyan.csrf.token_id'] = $this->random(10);
        }

        return $_SESSION['cyan.csrf.token_id'];
    }

    /**
     * Clear CSRF session
     *
     * @since 1.0.0
     */
    public function clear()
    {
        if (isset($_SESSION['cyan.csrf.token_id'])) {
            unset($_SESSION['cyan.csrf.token_id']);
        }
        if (isset($_SESSION['cyan.csrf.token_value'])) {
            unset($_SESSION['cyan.csrf.token_value']);
        }
    }

    /**
     * create token value
     *
     * @param $force_new
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getToken($force_new)
    {
        if (!isset($_SESSION['cyan.csrf.token_value']) || $force_new) {
            $token = hash('sha256', $this->random(500));
            $_SESSION['cyan.csrf.token_value'] = $token;
        }

        return $_SESSION['cyan.csrf.token_value'];
    }

    /**
     * Validate CSRF
     *
     * @param string $method
     *
     * @return bool
     *
     * @since 1.0.0
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
            default:
                throw new CsrfException(sprintf('Error: Invalid parameter. %s only accept "get" or "post" parameter.', __FUNCTION__));
                break;
        }

        $token_id = $this->getTokenID(false);
        $token_value = $this->getToken(false);

        if(isset($handle) && isset($handle[$token_id]) && ($handle[$token_id] == $token_value)) {
            $this->clear();
            return true;
        }

        $this->clear();
        return false;
    }

    /**
     * create a random token
     *
     * @param int $len
     *
     * @return string
     *
     * @since 1.0.0
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