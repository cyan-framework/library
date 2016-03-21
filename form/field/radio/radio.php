<?php
namespace Cyan\Framework;

/**
 * Class FormFieldRadio
 * @package Cyan\Framework
 * @since 1.0.0
 */
class FormFieldRadio extends FormField
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
     * render radio
     *
     * @return string
     *
     * @since 1.0.0
     */
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
                $field .= " name='{$this->getInputName()}'";
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