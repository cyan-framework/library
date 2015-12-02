<?php
namespace Cyan\Library;

class FormFieldHidden extends FormField
{
    protected $_unsetAttributes = [
        'description',
        'label',
        'type'
    ];

    public function renderField()
    {
        $attributes = $this->getAttributes();

        $field = "<input";

        $field .= " type='hidden'";
        $field .= " name='{$this->getControlName()}[{$this->getName()}]'";
        $field .= " value='{$this->getValue()}'";

        $field .= $this->getAttributesString();

        $field .= " />";

        return $field;
    }


	public function render()
    {
        return $this->renderField();
    }
}