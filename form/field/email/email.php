<?php
namespace Cyan\Library;

/**
 * Class FormFieldEmail
 * @package Cyan\Library
 * @since 1.0.0
 */
class FormFieldEmail extends FormField
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
     * Render email input
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function renderField()
    {
        $attributes = $this->getAttributes();

        $field = "<input";

        $field .= " type='email'";
        $field .= " name='{$this->getInputName()}'";
        $field .= " value='{$this->getValue()}'";
        $field .= ' '.$this->getAttributesString();

        $field .= " />";

        return $field;
    }
}