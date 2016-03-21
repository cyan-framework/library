<?php
namespace Cyan\Framework;

trait TraitError
{
    protected $errors = [];

    /**
     * set error
     *
     * @param $message
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setError($message)
    {
        $this->errors[] = $message;

        return $this;
    }


    /**
     * Return list of errors
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Check if has errors
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function hasErrors()
    {
        return count($this->errors) ? true : false ;
    }

    /**
     * Return errors as string
     *
     * @param string $separator
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getErrorsAsMessage($separator = ',')
    {
        return implode($separator, $this->errors);
    }
}