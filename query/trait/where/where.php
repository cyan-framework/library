<?php
namespace Cyan\Library;

/**
 * Class QueryTraitWhere
 * @package Cyan\Library
 */
trait QueryTraitWhere
{
    public function where($condition)
    {
        $this->statements['where'][] = $condition;
        return $this;
    }

    public function andWhere($condition)
    {
        $this->statements['where'][] = ' AND '.$condition;

        return $this;
    }

    public function orWhere($condition)
    {
        $this->statements['where'][] = ' OR '.$condition;

        return $this;
    }

    public function inWhere($field, $condition)
    {
        $this->statements['where'][] = sprintf(' IN (%s)',$condition);

        return $this;
    }

}