<?php
namespace Cyan\Framework;

/**
 * Class FormFieldLabel
 * @package Cyan\Framework
 * @since 1.0.0
 */
class FormFieldLabel extends FormField
{
    /**
     * label class
     *
     * @var string
     * @since 1.0.0
     */
    private static $label_class = '';

    /**
     * FormFieldLabel constructor.
     *
     * @param XmlElement $field
     *
     * @since 1.0.0
     */
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
            $attributes["class"] = self::$label_class;
        else
            $attributes["class"] = $fieldAttributes['class']." ".self::$label_class;

        $this->setAttributes($attributes);
    }

    /**
     * set label class
     *
     * @param $class_name
     *
     * @since 1.0.0
     */
    public static function setLabelClass($class_name)
    {
        self::$label_class = $class_name;
    }

    /**
     * Render a label
     *
     * @return string
     *
     * @since 1.0.0
     */
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

    /**
     * @return string
     */
    public function getValue()
    {
        $Cyan = \Cyan::initialize();

        if ($Cyan->hasContainer('application')) {
            $this->value = $Cyan->getContainer('application')->Text->translate($this->value);
        }

        return $this->value;
    }
}