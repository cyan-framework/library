<?php
namespace Cyan\Framework;

/**
 * Trait Prototype
 * @since 1.0.0
 */
trait TraitPrototype
{
    /**
     * Array of closure assigned to class
     *
     * @var array
     * @since 1.0.0
     */
    protected $prototype_closures = [];

    /**
     * Call Closure
     *
     * @param $name
     * @param $args
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function __call($name, $args) {
        if (isset($this->prototype_closures[$name]) && is_callable($this->prototype_closures[$name])) {
            return call_user_func_array($this->prototype_closures[$name], is_array($args) ? $args : [$args]);
        } else {
            Throw new \BadMethodCallException(sprintf('Undefined call "%s" in %s',$name,get_class($this)));
        }
    }

    /**
     * Set Data/Closure
     *
     * @param $name
     * @param $value
     *
     * @since 1.0.0
     */
    public function __set($name, $value) {
        if (method_exists($this, $name) && is_callable($value)) {
            Throw new TraitException(sprintf('Method "%s" already defined in %s',$name,get_class($this)));
        }

        if (is_callable($value)) {
            $this->prototype_closures[$name] = $value->bindTo($this, $this);
        } else {
            $this->$name = $value;
        }
    }
}
