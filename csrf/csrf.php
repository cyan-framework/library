<?php
namespace Cyan\Library;


/**
 * Class CSRF
 *
 * @package Cyan\Library
 */
class Csrf
{
    use TraitsSingleton;

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
    public function getInput(array $customAttributes = [], $forceNew = false)
    {
        $defaultAttributes = [
            'type' => 'hidden',
            'name' => $this->getTokenID($forceNew),
            'value' => $this->getToken($forceNew)
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
    public function getTokenID($forceNew)
    {
        if (!isset($_SESSION['token_id']) || $forceNew) {
            $token_id = $this->random(10);
            $_SESSION['token_id'] = $token_id;
        }

        return $_SESSION['token_id'];
    }

    /**
     * Clear CSRF session
     */
    public function clear()
    {
        if (isset($_SESSION['token_id'])) {
            unset($_SESSION['token_id']);
        }
        if (isset($_SESSION['token_value'])) {
            unset($_SESSION['token_value']);
        }
    }

    /**
     * @return string
     */
    public function getToken($forceNew)
    {
        if (!isset($_SESSION['token_value']) || $forceNew) {
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

        $tokenID = $this->getTokenID(false);
        $tokenValue = $this->getToken(false);

        if(isset($handle) && isset($handle[$tokenID]) && ($handle[$tokenID] == $tokenValue)) {
            $this->clear();
            return true;
        }

        $this->clear();
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