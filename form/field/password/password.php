<?php
namespace Cyan\Framework;

/**
 * Class FormFieldPassword
 * @package Cyan\Framework
 * @since 1.0.
 */
class FormFieldPassword extends FormField
{
    /**
     * Unset attributes
     *
     * @var array
     * @since 1.0.0
     */
    protected $unset_attributes = [
        'description',
        'label',
        'name',
        'type'
    ];

    /**
     * render password field
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function renderField()
    {
        $field = "<input";

        $field .= " type='password'";
        $field .= " name='{$this->getInputName()}'";
        $field .= " value='{$this->getValue()}'";

        $field .= ' '.$this->getAttributesString();

        $field .= " />";

        return $field;
    }
}