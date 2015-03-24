<?php
namespace Cyan\Library;

/**
 * Class QueryTraitWhere
 * @package Cyan\Library
 */
trait QueryTraitWhere
{
    public function where($condition, $parameters = null)
    {

        return $this;
    }

    public function andWhere()
    {

        return $this;
    }

    public function orWhere()
    {

        return $this;
    }
}