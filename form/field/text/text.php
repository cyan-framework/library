<?php
namespace Cyan\Library;

/**
 * Class FormFieldText
 * @package Cyan\Library
 */
class FormFieldText extends FormField
{
    /**
     * Unset attributes
     *
     * @var array
     */
    protected $_unsetAttributes = [
        "description",
        "label"
    ];

    /**
     * @return string
     */
    public function renderField()
    {
        $attributes = $this->getAttributes();

        $field = "<input";

        $field .= " type='text'";
        $field .= " name='{$this->getControlName()}[{$this->getName()}]'";
        $field .= " value='{$this->getValue()}'";

        $field .= $this->getAttributesString();

        $field .= " />";

        return $field;
    }
}