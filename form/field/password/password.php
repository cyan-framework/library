<?php
namespace Cyan\Library;

class FormFieldPassword extends FormField
{
    protected $_unsetAttributes = [
        'description',
        'label',
        'name'
    ];

    public function renderField()
    {
        $attributes = $this->getAttributes();

        $field = "<input";

        $field .= " type='password'";
        $field .= " name='{$this->getControlName()}[{$this->getName()}]'";
        $field .= " value='{$this->getValue()}'";

        $field .= $this->getAttributesString();

        $field .= " />";

        return $field;
    }
}