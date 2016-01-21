<?php
namespace Cyan\Library;

/**
 * Class FormFieldOption
 * @package Cyan\Library
 * @since 1.0.0
 */
class FormFieldOption extends FormField
{
    /**
     * Boolean selected
     *
     * @var bool
     * @since 1.0.0
     */
    private $selected = false;

    /**
     * Unset attributes
     *
     * @var array
     * @since 1.0.0
     */
    protected $unset_attributes = [
        'description',
        'label',
        'id',
        'name'
    ];

    /**
     * Option
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function renderField()
    {
        $attributes = $this->getAttributes();

        $field = "<option";

        $field .= " value='{$this->getValue()}'";

        $field .= $this->getAttributesString();

        if($this->selected)
            $field .= " selected='selected'";

        $field .= " >";
        $field .= $this->getName();
        $field .= "</option>";

        return $field;
    }

    /**
     * set selected
     *
     * @param $value
     *
     * @since 1.0.0
     */
    public function setSelected($value)
    {
        $this->selected = $value;
    }
}