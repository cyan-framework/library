<?php
namespace Cyan\Library;

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
     * print SQL
     */
    public function ___toString()
    {
        return $this->getQuery();
    }
}