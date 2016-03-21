<?php
namespace Cyan\Framework;

/**
 * Class DatabaseQueryInsert
 * @package Cyan\Framework
 * @since 1.0.0
 */
class DatabaseQueryInsert extends DatabaseQueryBase
{
    /**
     * Setup Table and alias if is defined
     *
     * @param $table
     * @param null $alias
     *
     * @since 1.0.0
     */
    public function __construct($table, $alias = null)
    {
        $this->table = $table;
        $this->table_alias = $alias;
    }

    /**
     * Select fields
     *
     * @param $columns
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function columns($columns)
    {
        $this->statements['columns'][] = $columns;

        return $this;
    }

    /**
     * Set values for insert
     *
     * @param $values
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function values()
    {
        $this->statements['values'] = func_get_args();

        return $this;
    }

    /**
     * Return SQL String
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getQuery()
    {
        if (!is_array($this->statements['values'])) {
            if (strpos($this->statements['values'][0],':') === false) {
                $values_condition = '","';
                $values_prefix = '"';
            } else {
                $values_condition = ',';
                $values_prefix = '';
            }
        } else {
            $values_condition = '","';
            $values_prefix = '"';
        }



        $sql =  sprintf('INSERT INTO %s (%s)', $this->table, implode(',',$this->statements['columns']));

        $insert_values = [];
        foreach ($this->statements['values'] as $insertData) {
            $insert_values[] = sprintf("({$values_prefix}%s{$values_prefix})", implode($values_condition, $insertData));
        }

        $sql .= sprintf('VALUES %s;',implode(',',$insert_values));

        return $sql;
    }

    /**
     * print SQL
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function __toString()
    {
        return $this->getQuery();
    }
}