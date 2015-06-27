<?php
namespace Cyan\Library;

/**
 * Class QuerySelect
 * @package Cyan\Library
 */
class QuerySelect extends QueryBase
{
    use QueryTraitWhere, QueryTraitOrder, QueryTraitGroup, QueryTraitLimit, QueryTraitJoin;

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
    public function select($columns)
    {
        $this->statements['select'] = $columns;

        return $this;
    }

    /**
     * Count a column
     *
     * @param $column
     * @return $this
     */
    public function count($column)
    {
        $this->statements['select'] = sprintf('COUNT(%s)',$column);

        return $this;
    }

    /**
     * Return SQL String
     *
     * @return string
     */
    public function getQuery()
    {
        $from = !empty($this->table_alias) ? sprintf('%s AS %s', $this->table, $this->table_alias) : $this->table ;

        $sql = sprintf('SELECT %s FROM %s', $this->statements['select'], $from);

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

        if (isset($this->statements['group'])) {
            $sql .= ' GROUP BY '.implode(',',$this->statements['group']);
        }

        if (isset($this->statements['order'])) {
            $sql .= ' ORDER BY '.implode(',',$this->statements['order']);
        }

        if (isset($this->offset) && isset($this->limit)) {
            $sql .= ' LIMIT '.$this->offset.', '.$this->limit;
        } elseif (isset($this->limit)) {
            $sql .= ' LIMIT '.$this->limit;
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