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
        if (!is_array($this->statements['values'])) {
            if (strpos($this->statements['values'][0],':') === false) {
                $valuesCondition = '","';
                $valuesPrefix = '"';
            } else {
                $valuesCondition = ',';
                $valuesPrefix = '';
            }
        } else {
            $valuesCondition = '","';
            $valuesPrefix = '"';
        }



        $sql =  sprintf('INSERT INTO %s (%s)', $this->table, implode(',',$this->statements['columns']));

        $insertValues = [];
        foreach ($this->statements['values'] as $insertData) {
            $insertValues[] = sprintf("({$valuesPrefix}%s{$valuesPrefix})", implode($valuesCondition, $insertData));
        }

        $sql .= sprintf('VALUES %s;',implode(',',$insertValues));

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