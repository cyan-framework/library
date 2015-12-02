<?php
namespace Cyan\Library;

class FormFieldCheckbox extends FormField
{
    protected $_unsetAttributes = [
        'description',
        'label',
        'type'
    ];

    public function renderField()
    {
        $attributes = $this->getAttributes();
        $options = $this->getOptions() ;

        $field = "";
        if(count($options) > 0)
        {
            foreach($options as $option)
            {
                $option->unsetAttributes(array('name'));
                $field .= "<input type='checkbox'";
                $field .= " name='{$this->getControlName()}[{$this->getName()}][]'";
                $field .= " value='{$option->getValue()}'";

                $field .= $option->getAttributesString();

                if($option->getValue() == $this->getValue())
                    $field .= " checked='checked'";

                $field .= " />";
                $field .= "&nbsp;".$option->getName();

                $field .= "<br clear='all' />";
            }
        }

        return $field;
    }


    public function setValue($values)
    {
        if(is_string($values))
            $values = explode(",", $values);

        parent::setValue($values);
    }
}