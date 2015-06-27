<?php
namespace Cyan\Library;

/**
 * Class QueryTraitGroup
 * @package Cyan\Library
 */
trait QueryTraitGroup
{
    public function groupBy($condition)
    {
        $this->statements['group'] = $condition;

        return $this;
    }
}