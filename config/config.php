<?php

namespace Cyan\Framework;

/**
 * Class Config
 * @package Cyan\Framework
 * @since 1.0.0
 *
 * @method Config getInstance
 */
class Config implements \ArrayAccess
{
    use TraitMultiton;

    /**
     * @var array
     * @since 1.0.0
     */
    private $data;

    /**
     * @var string
     * @since 1.0.0
     */
    private $separator = '.';

    /**
     * Load a config file
     *
     * @param $file_path
     * @param $format
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function loadFile($file_path, $format = '')
    {
        if (empty($format)) {
            $format = pathinfo(basename($file_path), PATHINFO_EXTENSION);
            if (!in_array($format,['php','json'])) $format = 'php';
        }

        switch ($format) {
            case 'php':
                $array = require_once $file_path;
                if (!is_array($array)) {
                    throw new ConfigException(sprintf('php file (%s) must return an array',$file_path));
                }

                $this->loadArray($array);
                break;
            case 'json':
                $this->loadString(file_get_contents($file_path));
                break;
        }

        return $this;
    }

    /**
     * Load form array
     *
     * @param $array
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function loadArray(array $array)
    {
        $this->bind($array);

        return $this;
    }

    /**
     * bind array
     *
     * @param array $data
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function bind(array $data)
    {
        $this->data = !empty($this->data) ? array_merge($this->data, $data) : $data;

        return $this;
    }

    /**
     * Load from json string
     *
     * @param $string
     * @param $format
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function loadString($string, $format = 'json')
    {
        switch (strtolower($format)) {
            case 'json':
                $array = json_decode($string, true);
                break;
        }

        $this->bind($array);

        return $this;
    }

    /**
     * @param $key
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function exists($key)
    {
        return !is_null($this->get($key));
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     *
     * @throws ConfigException
     *
     * @since 1.0.0
     */
    public function set($key, $value)
    {
        $node = &$this->data;

        foreach(explode('.', $key) as $step)
        {
            if (!isset($node[$step])) {
                $node[$step] = $value;
            }  elseif (is_array($node[$step])) {
                $node = &$this->data[$step];
            } else {
                throw new ConfigException(sprintf('Key %s is %s cant be defined',$key, gettype($node)));
            }
        }

        return $this;
    }

    /**
     * @param $key
     * @param null $default_value
     *
     * @return array|string|null
     *
     * @since 1.0.0
     */
    public function get($key, $default_value = null)
    {
        $node = $this->data;

        foreach (explode('.', $key) as $step) {
            if (!isset($node[$step])) {
                return $default_value;
            } else {
                $node = $node[$step];
            }
        }

        return $node;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->data = [];

        return $this;
    }

    /**
     * @param $key
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function remove($key)
    {
        $node = &$this->data;

        $keys = explode('.', $key);
        $totalKeys = count($keys) - 1;
        foreach ($keys as $key => $step) {
            if (isset($node[$step])) {
                if ($key == $totalKeys) {
                    unset($node[$step]);
                    return true;
                } else {
                    $node = &$node[$step];
                }
            }
        }

        return false;
    }

    /**
     * @param mixed $key
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function offsetUnset($key)
    {
        return $this->remove($key);
    }

    /**
     * @param mixed $key
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function offsetExists($key)
    {
        return $this->exists($key);
    }

    /**
     * @param mixed $key
     *
     * @return array|null|string
     *
     * @since 1.0.0
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @return Config
     *
     * @throws ConfigException
     *
     * @since 1.0.0
     */
    public function offsetSet($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * @return array
     *
     * @since 1.0.0
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @param $file_path
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function writeFile($format, $file_path)
    {
        switch ($format) {
            case 'php':
                if (!is_array($this->data)) {
                    throw new ConfigException('Config data must be an array to write php files');
                }

                $data = '<?php'.chr(13);
                $data .= 'return '.var_export($this->data,true).';'.chr(13);
                break;
            case 'json':
                $data = json_encode($this->data);
                break;
        }
        FilesystemPath::create(dirname($file_path));
        
        return file_put_contents($file_path,$data);
    }
}