<?php
namespace Cyan\Framework;

/**
 * Class DatabaseQueryTraitGroup
 * @package Cyan\Framework
 * @since 1.0.0
 */
trait DatabaseQueryTraitGroup
{
    /**
     * group by condition
     *
     * @param $condition
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function groupBy($condition)
    {
        $this->statements['group'] = $condition;

        return $this;
    }
}