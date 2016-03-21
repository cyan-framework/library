<?php
namespace Cyan\Framework;

/**
 * Class FormFieldTextarea
 * @package Cyan\Framework
 * @since 1.0.0
 */
class FormFieldTextarea extends FormField
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
        'type'
    ];

    /**
     * render a field
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function renderField()
    {
        $field = "<textarea ";

        $field .= " name='{$this->getInputName()}'";

        $field .= $this->getAttributesString();

        $field .= " >";
        $field .= $this->getValue();
        $field .= "</textarea>";

        return $field;
    }
}
