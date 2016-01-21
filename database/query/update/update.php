<?php
namespace Cyan\Library;

/**
 * Class DatabaseQueryUpdate
 * @package Cyan\Library
 * @since 1.0.0
 */
class DatabaseQueryUpdate extends DatabaseQueryBase
{
    use DatabaseQueryTraitWhere, DatabaseQueryTraitJoin, DatabaseQueryTraitSpecialfield;

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
     * @return $this
     *
     * @since 1.0.0
     */
    public function set()
    {
        $args = func_get_args();

        $set_args = [];
        foreach ($args as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $field => $value) {
                    $set_args[] = sprintf('%s="%s"', $field, addslashes($value));
                }
            } elseif (is_string($arg)) {
                $set_args[] = $arg;
            }
        }

        $this->statements['set'][] = implode(',',$set_args);

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
        $from = !empty($this->table_alias) ? sprintf('%s AS %s', $this->table, $this->table_alias) : $this->table ;

        $sql_parts = [
            'where' => []
        ];

        // catch all tables from join
        $join_tables_search = [];
        if (!empty($this->statements['join'])) {
            foreach ($this->statements['join'] as $join_type => $joins) {
                foreach ($joins as $join) {
                    $join_parts = explode(' on ',strtolower($join));
                    $join_table = explode(' as ',trim($join_parts[0]));
                    $join_tables_search[] = end($join_table);
                }
            }
        }

        $sql = sprintf('UPDATE %s SET %s', $from, implode(',',$this->statements['set']));

        if (isset($this->statements['where'])) {
            foreach ($this->statements['where'] as $where_condition) {
                $this->specialField($where_condition, $join_tables_search, $sql_parts['where']);
            }
        }

        if (isset($this->statements['join'])) {
            foreach ($this->statements['join'] as $join_type => $conditions) {
                foreach ($conditions as $condition) {
                    $sql .= sprintf(' %s JOIN %s ', $join_type, $condition);
                }
            }
        }

        if (!empty($sql_parts['where'])) {
            $sql .= ' WHERE '.implode('',$sql_parts['where']);
        }

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