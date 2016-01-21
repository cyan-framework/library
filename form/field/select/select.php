<?php
namespace Cyan\Library;

/**
 * Class FormFieldSelect
 * @package Cyan\Library
 * @since 1.0.0
 */
class FormFieldSelect extends FormField
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
        'type',
        'value'
    ];

    /**
     * render select
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function renderField()
    {
        $field = "<select";

        $field .= " name='{$this->getInputName()}'";

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