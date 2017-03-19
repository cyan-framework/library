<?php
namespace Cyan\Framework;

/**
 * Class FormField
 * @package Cyan\Framework
 * @since 1.0.0
 */
class FormField
{
    /**
     * Name of input
     *
     * @var string
     * @since 1.0.0
     */
    protected $name = null;

    /**
     * Value of input
     *
     * @var string
     * @since 1.0.0
     */
    protected $value = null;

    /**
     * attributes
     *
     * @var array
     * @since 1.0.0
     */
    protected $attributes = [];

    /**
     * Label Field
     *
     * @var FormFieldLabel
     * @since 1.0.0
     */
    protected $label = null;

    /**
     * Options list
     * @var array
     * @since 1.0.0
     */
    protected $options = [];

    /**
     * Form control_name
     *
     * @var string
     * @since 1.0.0
     */
    protected $control_name = '';

    /**
     * Render template for render() method
     *
     * @var string
     * @since 1.0.0
     */
    protected static $render_template = '%s&nbsp;%s';

    /**
     * List of attributes to be unset after constructor.
     *
     * @var array
     * @since 1.0.0
     */
    protected $unset_attributes = [];

    public function __construct(XmlElement $node, $control_name = '', $options = null)
    {
        $this->control_name = $control_name;

        $attributes = $node->getAttributes();

        // unset cf-namespace attribute
        if (isset($attributes['cf-namespace'])) {
            unset($attributes['cf-namespace']);
        }

        if(array_key_exists("name", $attributes))
            $this->name = $attributes["name"];

        if(isset($attributes["value"]))
            $this->value = $attributes["value"];

        if(!isset($attributes["id"]))
            $attributes["id"] = $attributes["name"]."_id";

        unset($attributes["value"]);
        if(!is_null($options)) {
            $this->options = $options;
        }

        $this->attributes = $attributes;

        $this->label = new FormFieldLabel($this);

        if (!empty($this->unset_attributes)) {
            $this->unsetAttributes($this->unset_attributes);
        }

        $this->initialize();
    }

    /**
     * Return input name
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getInputName()
    {
        if (!empty($this->control_name)) {
            $template = ($this->getAttribute('multiple')) ? '%s[%s][]' : '%s[%s]';
            $input_name = sprintf($template, $this->control_name, $this->name);
        } else {
            $template = ($this->getAttribute('multiple')) ? '%s[]' : '%s[]';
            $input_name = sprintf($template, $this->name);
        }

        return $input_name;
    }

    /**
     * Custom code
     *
     * @since 1.0.0
     */
    protected function initialize()
    {

    }

    /**
     * Return Label Field
     *
     * @return FormFieldLabel
     *
     * @since 1.0.0
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * get a attribute from field
     *
     * @param $attribute
     *
     * @return string|null
     *
     * @since 1.0.0
     */
    public function getAttribute($attribute)
    {
        $attribute = strtolower($attribute);
        if(array_key_exists($attribute, $this->attributes))
            return $this->attributes[$attribute];
        else
            return null;
    }

    /**
     * Set an attribute to field
     *
     * @param $attribute_name
     * @param $attribute_value
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setAttribute($attribute_name, $attribute_value)
    {
        $this->attributes[$attribute_name] = $attribute_value;

        return $this;
    }

    /**
     * Return array of attributes
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set array of attributes by array key => value
     *
     * @param array $attributes
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setAttributes(array $attributes)
    {
        foreach($attributes as $attribute_name => $attribute_value)
            $this->setAttribute($attribute_name, $attribute_value);

        return $this;
    }

    /**
     * Set Options for field
     *
     * @param array $options
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setOptions(array $options)
    {
        $elements = array();
        foreach($options as $option)
        {
            $option->attributes["name"] = $option->text;
            $option->attributes["value"] = $option->value;

            $option = new FormFieldOption($option->attributes);
            $elements[] = $option;
        }

        $this->options = !empty($this->options) ? array_merge($this->options,$elements) : $elements;

        return $this;
    }

    /**
     * Return array of options
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Input name
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Input Value
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set input value
     *
     * @param $value
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get control_name
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getControlName()
    {
        return $this->control_name;
    }

    /**
     * Define a template string to field
     *
     * @param $template_string
     *
     * @since 1.0.0
     */
    public static function renderTemplate($template_string)
    {
        self::$render_template = $template_string;
    }

    /**
     * Render label and input
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function render()
    {
        $label = $this->label->renderField();

        $field = $this->renderField();

        return sprintf(self::$render_template,$label,$field);
    }

    /**
     * Render a label field
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function renderLabel()
    {
        $label = $this->label->renderField();

        return $label;
    }

    /**
     * Remove single attribute
     *
     * @param string $attribute
     * @return $this
     */
    public function unsetAttribute($attribute)
    {
        if (isset($this->attributes[$attribute])) {
            unset($this->attributes[$attribute]);
        }

        return $this;
    }

    /**
     * unset field attributes
     *
     * @param array $attributes
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function unsetAttributes(array $attributes)
    {
        foreach($attributes as $attribute)
            unset($this->attributes[$attribute]);

        return $this;
    }

    /**
     * return attribute string
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getAttributesString()
    {
        return empty($this->attributes) ? '' : implode(' ', array_map(function ($k, $v) { return $k .'="'. htmlspecialchars($v) .'"'; },array_keys($this->attributes), $this->attributes));
    }
}
