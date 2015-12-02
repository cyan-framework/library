<?php
namespace Cyan\Library;

class XmlElement extends \SimpleXMLElement
{
    /**
     * List of all attributes
     *
     * @return array
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
     * @return null|string
     */
    public function getAttribute($name, $default_value = null)
    {
        return isset($this[$name]) ? (string)$this[$name][0] : $default_value  ;
    }
}