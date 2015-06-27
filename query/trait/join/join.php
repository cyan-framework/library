<?php
namespace Cyan\Library;

/**
 * Class QueryTraitJoin
 * @package Cyan\Library
 */
trait QueryTraitJoin
{
    /**
     * Generic JOIN
     *
     * @param $type
     *
     * @param $table
     *
     * @param $condition
     *
     * @return $this
     */
    public function join($type, $table, $condition)
    {
        $this->statements['join'][$type][] = ' '.$table.' ON '.$condition;
        
        return $this;
    }

    /**
     * Left Join a SQL
     *
     * @param $joinedTable
     *
     * @param $condition
     *
     * @return QueryTraitJoin
     */
    public function leftJoin($joinedTable, $condition)
    {
        return $this->join('left', $joinedTable, $condition);
    }

    /**
     * Right Join a SQL
     *
     * @param $joinedTable
     *
     * @param $condition
     *
     * @return QueryTraitJoin
     */
    public function rightJoin($joinedTable, $condition)
    {
        return $this->join('right', $joinedTable, $condition);
    }

    /**
     * Inner Join a SQL
     *
     * @param $joinedTable
     *
     * @param $condition
     *
     * @return QueryTraitJoin
     */
    public function innerJoint($joinedTable, $condition)
    {
        return $this->join('inner', $joinedTable, $condition);
    }
}