<?php
namespace Cyan\Library;

/**
 * Class DatabaseQuery
 * @package Cyan\Library
 * @since 1.0.0
 */
class DatabaseQuery
{
    use TraitSingleton;

    /**
     * Create Select Query
     *
     * @param $table
     * @param null $alias
     *
     * @return DatabaseQuerySelect
     *
     * @since 1.0.0
     */
    public function from($table, $alias = null)
    {
        return new DatabaseQuerySelect($table, $alias);
    }

    /**
     * Create Insert Query
     *
     * @param $table
     *
     * @return DatabaseQueryInsert
     *
     * @since 1.0.0
     */
    public function insert($table)
    {
        return new DatabaseQueryInsert($table);
    }

    /**
     * Create Update Query
     *
     * @param $table
     *
     * @return DatabaseQueryUpdate
     *
     * @since 1.0.0
     */
    public function update($table)
    {
        return new DatabaseQueryUpdate($table);
    }

    /**
     * Create Delete Query
     *
     * @param $table
     *
     * @return DatabaseQueryDelete
     *
     * @since 1.0.0
     */
    public function delete($table)
    {
        return new DatabaseQueryDelete($table);
    }
}