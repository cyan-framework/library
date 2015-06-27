<?php
namespace Cyan\Library;

/**
 * Class QueryInsert
 * @package Cyan\Library
 */
class QueryInsert extends QueryBase
{
    /**
     * Setup Table and alias if is defined
     *
     * @param $table
     * @param null $alias
     */
    public function __construct($table, $alias = null)
    {
        $this->table = $table;
        $this->table_alias = $alias;
    }

    /**
     * Select fields
     *
     * @param $fields
     * @return $this
     */
    public function columns($columns)
    {
        $this->statements['columns'] = $columns;

        return $this;
    }

    public function values($values)
    {
        $this->statements['values'] = $values;

        return $this;
    }

    /**
     * Return SQL String
     *
     * @return string
     */
    public function getQuery()
    {
        if (strpos($this->statements['values'][0],':') === false) {
            $valuesCondition = '","';
            $valuesPrefix = '"';
        } else {
            $valuesCondition = ',';
            $valuesPrefix = '';
        }

        $sql = sprintf('INSERT INTO %s (%s) VALUES ('.$valuesPrefix.'%s'.$valuesPrefix.')', $this->table, implode(',',$this->statements['columns']), implode($valuesCondition, $this->statements['values']));

        return $sql;
    }

    /**
     * print SQL
     */
    public function __toString()
    {
        return $this->getQuery();
    }
}