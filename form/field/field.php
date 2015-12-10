<?php
namespace Cyan\Library;

/**
 * Class FormField
 * @package Cyan\Library
 */
class FormField
{
    private $_name = null;
    private $_value = null;
    private $_attributes = [];
    private $_label = null;
    private $_options = null;
    private $_controlName = null;
    protected $_unsetAttributes = [];

    public function __construct(XmlElement $node, $controlName = "fields", $options = null)
    {
        $this->_controlName = $controlName;

        $attributes = $node->getAttributes();

        if(array_key_exists("name", $attributes))
            $this->_name = $attributes["name"];

        if(isset($attributes["value"]))
            $this->_value = $attributes["value"];

        if(!isset($attributes["id"]))
            $attributes["id"] = $attributes["name"]."_id";

        unset($attributes["value"]);
        if(!is_null($options))
            $this->_options = $options;

        $this->_attributes = $attributes;
        $this->_label = new FormFieldLabel($this);

        if (!empty($this->_unsetAttributes)) {
            $this->unsetAttributes($this->_unsetAttributes);
        }
    }

    protected function initialize()
    {

    }

    public function getLabel()
    {
        return $this->_label;
    }


    public function getAttribute($attribute)
    {
        $attribute = strtolower($attribute);
        if(array_key_exists($attribute, $this->_attributes))
            return $this->_attributes[$attribute];
        else
            return null;
    }


    public function setAttribute($attributeName, $attributeValue)
    {
        $this->_attributes[$attributeName] = $attributeValue;

        return $this;
    }


    public function getAttributes()
    {
        return $this->_attributes;
    }


    public function setAttributes(array $attributes)
    {
        foreach($attributes as $attributeName => $attributeValue)
            $this->setAttribute($attributeName, $attributeValue);

        return $this;
    }


    public function setOptions(array $options)
    {
        $elements = array();
        foreach($options as $option)
        {
            $option->_attributes["name"] = $option->text;
            $option->_attributes["value"] = $option->value;

            $option = new FormFieldOption($option->_attributes);
            $elements[] = $option;
        }

        $this->_options = $elements;

        return $this;
    }


    public function getOptions()
    {
        return $this->_options;
    }


    public function getName()
    {
        return $this->_name;
    }


    public function getValue()
    {
        return $this->_value;
    }


    public function setValue($value)
    {
        $this->_value = $value;

        return $this;
    }


    public function getControlName()
    {
        return $this->_controlName;
    }


    public function render()
    {
        $label = $this->_label->renderField();

        $field = $this->renderField();

        return $label."&nbsp;".$field;
    }


    public function renderLabel()
    {
        $label = $this->_label->renderField();

        return $label;
    }

    protected function unsetAttributes(array $attributes)
    {
        foreach($attributes as $attribute)
            unset($this->_attributes[$attribute]);

        return $this;
    }


    public function getAttributesString()
    {
        $attributes = $this->_attributes;
        $strAttributes = "";
        foreach($attributes as $attributeName => $attributeValue)
            $strAttributes .= " ".$attributeName.'="'.$attributeValue.'"';

        return $strAttributes;
    }
}