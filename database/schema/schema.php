<?php
namespace Cyan\Library;

/**
 * Class DatabaseSchema
 * @package Cyan\Library
 * @since 1.0.0
 *
 * @method DatabaseSchema getInstance
 */
class DatabaseSchema
{
    use TraitSingleton;

    /**
     * Schema Information Array
     *
     * @var array
     * @since 1.0.0
     */
    protected $schema = [
        'alias' => [],
        'primary' => [],
        'reference' => [],
        'backreference' => []
    ];

    /**
     * crete a alias for a table
     *
     * @param $alias
     * @param $table
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setAlias( $alias, $table )
    {
        $this->schema['alias'][$table] = $alias;

        return $this;
    }

    /**
     * get a alias based on table name
     *
     * @param $table
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function getAlias($table)
    {
        return isset( $this->schema['alias'][$table] ) ? $this->schema['alias'][$table] : $table;
    }

    /**
     * check if string is an alias
     *
     * @param $alias
     * @return bool
     */
    public function isAlias($alias)
    {
        return array_search($alias,$this->schema['alias']) !== false ? true : false;
    }

    /**
     * get table name based on alias
     *
     * @param $alias
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getTable($alias)
    {
        $table = array_search($alias,$this->schema['alias']);
        if ($table !== false) {
            return $table;
        }

        return $alias;
    }

    /**
     * set primary for a table
     *
     * @param $table
     * @param $column
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setPrimary( $table, $column )
    {
        $this->schema['primary'][$table] = $column;

        return $this;
    }

    /**
     * get a primary from table
     *
     * @param $table
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function getPrimary( $table )
    {
        return isset($this->schema['primary'][$table]) ? $this->schema['primary'][$table] : 'id' ;
    }

    /**
     * set a reference column from a table relation
     *
     * @param $table
     * @param $name
     * @param $column
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setReference( $table, $name, $column )
    {
        $this->schema['reference'][ $table ][ $name ] = $column;

        return $this;
    }

    /**
     * get a reference column frlom a table relation
     *
     * @param $table
     * @param $name
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getReference($table, $name)
    {
        return isset($this->schema['reference'][ $table ][ $name ]) ? $this->schema['reference'][ $table ][ $name ] : $name . '_id' ;
    }

    /**
     * set a back reference for a column
     *
     * @param $table
     * @param $name
     * @param $column
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setBackReference( $table, $name, $column )
    {
        $this->schema['backreference'][ $table ][ $name ] = $column;

        return $this;
    }

    /**
     * get a back reference from a table relation
     *
     * @param $table
     * @param $name
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getBackReference($table, $name)
    {
        return isset( $this->schema['backreference'][ $table ][ $name ] ) ? $this->schema['backreference'][ $table ][ $name ] : $table . '_id' ;
    }
}