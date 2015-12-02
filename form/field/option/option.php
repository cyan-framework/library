<?php
namespace Cyan\Library;

class FormFieldOption extends FormField
{
    private $_selected = false;

    /**
     * Unset attributes
     *
     * @var array
     */
    protected $_unsetAttributes = [
        'description',
        'label',
        'id',
        'name'
    ];

    public function renderField()
    {
        $attributes = $this->getAttributes();

        $field = "<option";

        $field .= " value='{$this->getValue()}'";

        $field .= $this->getAttributesString();

        if($this->_selected)
            $field .= " selected='selected'";

        $field .= " >";
        $field .= $this->getName();
        $field .= "</option>";

        return $field;
    }


    public function setSelected($value)
    {
        $this->_selected = $value;
    }
}