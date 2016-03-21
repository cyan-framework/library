<?php
namespace Cyan\Framework;

/**
 * Class FormFieldText
 * @package Cyan\Framework
 * @since 1.0.0
 */
class FormFieldText extends FormField
{
    /**
     * Unset attributes
     *
     * @var array
     * @since 1.0.0
     */
    protected $unset_attributes = [
        "description",
        "label",
        "name",
        "type"
    ];

    /**
     * Return Field
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function renderField()
    {
        $field = "<input";

        $field .= " type='text'";
        $field .= " name='{$this->getInputName()}'";
        $field .= " value='{$this->getValue()}'";

        $field .= ' '.$this->getAttributesString();

        $field .= " />";

        return $field;
    }
}