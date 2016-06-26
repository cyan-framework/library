<?php
namespace Cyan\Framework;

/**
 * Class DatabaseQuerySelect
 * @package Cyan\Framework
 */
class DatabaseQuerySelect extends DatabaseQueryBase
{
    use DatabaseQueryTraitWhere, DatabaseQueryTraitOrder, DatabaseQueryTraitGroup, DatabaseQueryTraitLimit, DatabaseQueryTraitJoin, DatabaseQueryTraitSpecialfield;

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

        $this->schema = DatabaseSchema::getInstance();
        if (!empty($alias)) {
            $this->schema->setAlias($alias, $table);
        }
    }

    /**
     * Select fields
     *
     * @param $columns
     * @return $this
     */
    public function select($columns)
    {
        $this->statements['select'][] = $columns;

        return $this;
    }

    /**
     * Select fields
     *
     * @param $columns
     * @return $this
     */
    public function selectRaw($columns)
    {
        $this->statements['select_raw'][] = $columns;

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
        $this->statements['count'] = sprintf('COUNT(%s)',$column);

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

        if (!isset($this->statements['select_raw'])) {
            $this->statements['select_raw'] = [];
        }

        $sql_parts = [
            'select' => $this->statements['select_raw'],
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

        if (empty($this->statements['select'])) {
            $this->select('*');
        }
        if (!empty($this->statements['select'])) {
            foreach ($this->statements['select'] as $selectField) {
                $fields = explode(',',$selectField);
                foreach ($fields as $field) {
                    $this->specialField($field, $join_tables_search, $sql_parts['select'], true);
                }
            }
        }

        $sql = sprintf('SELECT %s FROM %s', implode(',',$sql_parts['select']), $from);

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