<?php
namespace Cyan\Library;

class FormFieldRadio extends FormField
{
    protected $_unsetAttributes = [
        'description',
        'label',
        'type'
    ];

    public function renderField()
    {
        $attributes = $this->getAttributes();

        $options = $this->getOptions();
        $field = "";
        if(count($options) > 0)
        {
            foreach($options as $option)
            {
                $option->unsetAttributes(array('name'));
                $field .= "<p><input type='radio'";
                $field .= " name='{$this->getControlName()}[{$this->getName()}]'";
                $field .= " value='{$option->getValue()}'";
                $field .= " id='{$attributes["id"]}_{$option->getName()}'";

                $field .= $option->getAttributesString();

                if($option->getValue() == $this->getValue())
                    $field .= " checked='checked'";

                $field .= " />";

                $field .= "<label for='{$attributes["id"]}_{$option->getName()}'>".$option->getName()."</label><div class='clr'></div></p>";
            }
        }

        return $field;
    }
}