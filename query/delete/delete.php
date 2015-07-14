<?php
namespace Cyan\Library;

/**
 * Class QueryDelete
 * @package Cyan\Library
 */
class QueryDelete extends QueryBase
{
    use QueryTraitWhere, QueryTraitJoin;

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
     * Return SQL String
     *
     * @return string
     */
    public function getQuery()
    {
        $from = !empty($this->table_alias) ? sprintf('%s AS %s', $this->table, $this->table_alias) : $this->table ;

        $sql = sprintf('DELETE FROM %s', $from);

        if (isset($this->statements['join'])) {
            foreach ($this->statements['join'] as $joinType => $conditions) {
                foreach ($conditions as $condition) {
                    $sql .= sprintf(' %s JOIN %s ', $joinType, $condition);
                }
            }
        }

        if (isset($this->statements['where'])) {
            $sql .= ' WHERE ';
            foreach ($this->statements['where'] as $whereCondition) {
                $sql .= $whereCondition;
            }
        }

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