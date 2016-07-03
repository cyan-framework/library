<?php
namespace Cyan\Framework;

/**
 * Class FormFieldSelect
 * @package Cyan\Framework
 * @since 1.0.0
 */
class FormFieldDbdriver extends FormFieldSelect
{
    /**
     * Return array of options
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getOptions()
    {
        $pdoDrivers = \PDO::getAvailableDrivers();
        $availableDrivers = ['mysql'];

        foreach ($pdoDrivers as $pdoDriver) {
            if (!in_array($pdoDriver,$availableDrivers)) continue;
            $this->options[] = new FormFieldOption(new XmlElement('<option name="'.$pdoDriver.'" value="'.$pdoDriver.'"></option>'));
        }

        return $this->options;
    }
}