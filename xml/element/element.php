<?php
namespace Cyan\Framework;

/**
 * Class XmlElement
 * @package Cyan\Framework
 * @since 1.0.0
 */
class XmlElement extends \SimpleXMLElement
{
    /**
     * List of all attributes
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getAttributes()
    {
        $attributes = [];
        foreach ($this->attributes() as $key => $value) {
            $attributes[(string)$key] = (string)$value;
        }

        return $attributes;
    }

    /**
     * Return an attribute
     *
     * @param $name
     * @param null $default_value
     *
     * @return null|string
     *
     * @since 1.0.0
     */
    public function getAttribute($name, $default_value = null)
    {
        return isset($this[$name]) ? (string)$this[$name][0] : $default_value  ;
    }
}