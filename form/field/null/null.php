<?php
namespace Cyan\Library;

class FormFieldNull extends FormField
{
    public function renderField()
    {
        return "Element not defined for type: <b>".$this->getAttribute('type')."</b>";
    }

    public function render()
    {
        return $this->renderField();
    }
}