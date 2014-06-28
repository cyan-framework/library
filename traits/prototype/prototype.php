<?php
namespace Cyan\Library;

/**
 * Trait Prototype
 */
trait TraitsPrototype
{
    /**
     * Call Closure
     *
     * @param $name
     * @param $args
     * @return mixed
     */
    public function __call($name, $args) {
        if (isset($this->$name) && is_callable($this->$name)) {
            return call_user_func_array($this->$name, is_array($args) ? $args : array($args));
        } else {
            Throw new TraitsException(sprintf('Undefined call "%s" in %s',$name,get_class($this)));
        }
    }

    /**
     * Set Data/Closure
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value) {
        $this->$name = is_callable($value)?
            $value->bindTo($this, $this):
            $value;
    }
}