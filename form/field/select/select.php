<?php
namespace Cyan\Library;

class FormFieldSelect extends FormField
{
    protected $_unsetAttributes = [
        'description',
        'label',
        'type',
        'value'
    ];

    public function renderField()
    {
        $attributes = $this->getAttributes();

        $field = "<select";

        $field .= " name='{$this->getControlName()}[{$this->getName()}]'";

        $field .= $this->getAttributesString();

        $field .= " >";

        $value = $this->getValue();
        $options = $this->getOptions();

        if(count($options) > 0)
        {
            foreach($options as $option)
            {
                if($option->getValue() == $value)
                    $option->setSelected(true);

                $field .= $option->render();
            }
        }

        $field .= "</select>";

        return $field;
    }
}