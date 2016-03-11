<?php

namespace Cyan\Library;

/**
 * Class Config
 * @package Cyan\Library
 * @since 1.0.0
 *
 * @method Config getInstance
 */
class Config
{
    use TraitSingleton;

    /**
     * @var \stdClass
     * @since 1.0.0
     */
    private $object;

    /**
     * Load a config file
     *
     * @param $file_path
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function loadFile($file_path)
    {
        switch (pathinfo(basename($file_path), PATHINFO_EXTENSION)) {
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
    public function loadArray($array)
    {
        $this->object = !empty($this->object) ? (object) array_merge_recursive((array)$this->object, $array) : $array;

        return $this;
    }

    /**
     * Load from json string
     *
     * @param $json_string
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function loadString($json_string)
    {
        $this->object = !empty($this->object) ? (object) array_merge_recursive((array)$this->object, json_decode($json_string, true)) : json_decode($json_string);

        return $this;
    }

    /**
     * Read object from dot notation
     *
     * @param $identifier
     * @param null $default_value
     * @return \stdClass
     *
     * @since 1.0.0
     */
    public function get($identifier, $default_value = null)
    {
        if (strpos($identifier,'.')) {
            $node = $this->object;
            foreach (explode('.',$identifier) as $attribute) {
                $node = $this->getAttribute($node, $attribute, $default_value);
            }
        } else {
            $node = $this->getAttribute($this->object, $identifier, $default_value);
        }

        return $node;
    }

    /**
     * Return and attribute
     *
     * @param $node
     * @param $attribute_name
     * @param $default_value
     *
     * @since 1.0.0
     */
    private function getAttribute($node, $attribute_name, $default_value)
    {
        if (is_object($node)) {
            if (isset($node->$attribute_name)) {
                return $node->$attribute_name;
            }
        } elseif (is_array($node)) {
            if (isset($node[$attribute_name])) {
                return $node[$attribute_name];
            }
        }


        return $default_value;
    }
}