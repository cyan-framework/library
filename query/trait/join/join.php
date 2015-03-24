<?php
namespace Cyan\Library;

/**
 * Class QueryTraitJoin
 * @package Cyan\Library
 */
trait QueryTraitJoin
{
    public function join($type, $table, $condition)
    {

        
        return $this;
    }

    public function leftJoin($joinedTable)
    {


        return $this;
    }

    public function rightJoin($joinedTable)
    {


        return $this;
    }

    public function innerJoint($joinedTable)
    {


        return $this;
    }
}