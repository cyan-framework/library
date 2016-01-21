<?php
namespace Cyan\Library;

/**
 * Class QueryDelete
 * @package Cyan\Library
 * @since 1.0.0
 */
class DatabaseQueryDelete extends DatabaseQueryBase
{
    use DatabaseQueryTraitWhere, DatabaseQueryTraitJoin;

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
     * Return SQL String
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getQuery()
    {
        $from = !empty($this->table_alias) ? sprintf('%s AS %s', $this->table, $this->table_alias) : $this->table ;

        $sql = sprintf('DELETE FROM %s', $from);

        if (isset($this->statements['join'])) {
            foreach ($this->statements['join'] as $join_type => $conditions) {
                foreach ($conditions as $condition) {
                    $sql .= sprintf(' %s JOIN %s ', $join_type, $condition);
                }
            }
        }

        if (isset($this->statements['where'])) {
            $sql .= ' WHERE ';
            foreach ($this->statements['where'] as $where_condition) {
                $sql .= $where_condition;
            }
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
