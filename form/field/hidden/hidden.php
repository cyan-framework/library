<?php
namespace Cyan\Library;

/**
 * Class FormFieldHidden
 * @package Cyan\Library
 * @since 1.0.0
 */
class FormFieldHidden extends FormField
{
    /**
     * Unset attributes for hidden field
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
     * Render input hidden
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function renderField()
    {
        $attributes = $this->getAttributes();

        $field = "<input";

        $field .= " type='hidden'";
        $field .= " name='{$this->getInputName()}'";
        $field .= " value='{$this->getValue()}'";

        $field .= $this->getAttributesString();

        $field .= " />";

        return $field;
    }

    /**
     * Render hidden field without label always
     *
     * @return string
     *
     * @since 1.0.0
     */
	public function render()
    {
        return $this->renderField();
    }
}