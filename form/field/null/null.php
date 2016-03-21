<?php
namespace Cyan\Framework;

/**
 * Class FormFieldNull
 * @package Cyan\Framework
 * @since 1.0.0
 */
class FormFieldNull extends FormField
{
    /**
     * Render Null element
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function renderField()
    {
        return "Element not defined for type: <b>".$this->getAttribute('type')."</b>";
    }

    /**
     * render field only
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function render()
    {
        return $this->renderField();
    }
}