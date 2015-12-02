<?php
namespace Cyan\Library;

class FormFieldLabel extends FormField
{
    public static $labelClass = '';

    public function __construct($field)
    {
        $fieldAttributes = $field->getAttributes();
        $attributes = $this->getAttributes();

        $this->unsetAttributes(array('name'));

        if(isset($fieldAttributes["label"]))
            $this->setValue($fieldAttributes["label"]);

        $attributes["for"] = $fieldAttributes["id"];

        $attributes['id'] = str_replace("_id", "_lbl", $fieldAttributes['id']);

        if(empty($attributes['class']))
            $attributes["class"] = self::$labelClass;
        else
            $attributes["class"] = $fieldAttributes['class']." ".self::$labelClass;

        $this->setAttributes($attributes);
    }

    public function renderField()
    {
        $attributes = $this->getAttributes();

        $field = "<label ";

        $field .= $this->getAttributesString();

        $field .=" >";
        $field .= $this->getValue();
        $field .= "</label>";

        return $field;
    }
}