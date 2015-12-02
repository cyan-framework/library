<?php
namespace Cyan\Library;

class FormFieldTextarea extends FormField
{
    protected $_unsetAttributes = [
        'description',
        'label',
        'type'
    ];

    public function renderField()
    {


        $attributes = $this->getAttributes();

        $field = "<textarea ";

        $field .= " name='{$this->getControlname()}[{$this->getName()}]'";

        $field .= $this->getAttributesString();

        $field .= " >";
        $field .= $this->getValue();
        $field .= "</textarea>";

        return $field;
    }
}