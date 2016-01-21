<?php
namespace Cyan\Library;

/**
 * Class FormFieldCheckbox
 * @package Cyan\Library
 * @since 1.0.0
 */
class FormFieldCheckbox extends FormField
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
     * Render checkbox input
     *
     * @return string
     *
     * @since 1.0.0
     */
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
                $field .= " name='{$this->getInputName()}'";
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

    /**
     * define input values
     *
     * @param $values
     *
     * @since 1.0.0
     */
    public function setValue($values)
    {
        if(is_string($values))
            $values = explode(",", $values);

        parent::setValue($values);
    }
}