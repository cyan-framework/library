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
            return call_user_func_array($this->$name, is_array($args) ? $args : [$args]);
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
        if (method_exists($this, $name) && is_callable($value)) {
            Throw new TraitsException(sprintf('Method "%s" already defined in %s',$name,get_class($this)));
        }

        $this->$name = is_callable($value) ? $value->bindTo($this, $this) : $value;
    }
}