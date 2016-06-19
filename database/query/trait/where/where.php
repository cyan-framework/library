<?php
namespace Cyan\Framework;

/**
 * Class DatabaseQueryTraitWhere
 * @package Cyan\Framework
 * @since 1.0.0
 */
trait DatabaseQueryTraitWhere
{
    /**
     * define where condition
     *
     * @param $condition
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function where($condition)
    {
        $this->statements['where'][] = (isset($this->statements['where']) && count($this->statements['where'])) ? ' AND '.$condition : $condition;

        return $this;
    }

    /**
     * define where and condition
     *
     * @param $condition
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function andWhere($condition)
    {
        $this->statements['where'][] = ' AND '.$condition;

        return $this;
    }

    /**
     * define where or condition
     *
     * @param $condition
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function orWhere($condition)
    {
        $this->statements['where'][] = ' OR '.$condition;

        return $this;
    }

    /**
     * define where field IN condition
     *
     * @param $field
     * @param $condition
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function inWhere($field, $condition)
    {
        $this->statements['where'][] = sprintf('%s IN (%s)',$field,$condition);

        return $this;
    }
}