<?php
namespace Cyan\Library;

/**
 * Class DatabaseQueryTraitGroup
 * @package Cyan\Library
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