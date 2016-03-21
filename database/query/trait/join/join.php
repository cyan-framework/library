<?php
namespace Cyan\Framework;

/**
 * Class DatabaseQueryTraitJoin
 * @package Cyan\Framework
 * @since 1.0.0
 */
trait DatabaseQueryTraitJoin
{
    /**
     * Generic JOIN
     *
     * @param $type
     * @param $table
     * @param $condition
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function join($type, $table, $condition)
    {
        $type = strtoupper($type);
        $this->statements['join'][$type][] = ' '.$table.' ON '.$condition;
        
        return $this;
    }

    /**
     * Left Join a SQL
     *
     * @param $joined_table
     * @param $condition
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function leftJoin($joined_table, $condition)
    {
        return $this->join('left', $joined_table, $condition);
    }

    /**
     * Right Join a SQL
     *
     * @param $joined_table
     * @param $condition
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function rightJoin($joined_table, $condition)
    {
        return $this->join('right', $joined_table, $condition);
    }

    /**
     * Inner Join a SQL
     *
     * @param $joined_table
     * @param $condition
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function innerJoint($joined_table, $condition)
    {
        return $this->join('inner', $joined_table, $condition);
    }
}