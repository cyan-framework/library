<?php
namespace Cyan\Library;

/**
 * Class Query
 * @package Cyan\Library
 */
class Query
{
    /**
     * PDO DB Access
     *
     * @var array
     */
    private $pdo = [
        'read' => null,
        'write' => null
    ];

    /**
     * Singleton Instance
     *
     * @var self
     */
    private static $instance;

    /**
     * Singleton Instance
     *
     * @param array $config
     * @return self
     */
    public static function getInstance(array $config) {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Constructor Query
     *
     * @param array $config
     */
    public function __construct(array $config) {
        $this->pdo = $config;
    }

    /**
     * Create Select Query
     *
     * @param $table
     * @param null $alias
     * @return QuerySelect
     */
    public function from($table, $alias = null)
    {
        return new QuerySelect($table, $alias);
    }

    /**
     * Create Insert Query
     *
     * @param $table
     * @return QueryInsert
     */
    public function insert($table)
    {
        return new QueryInsert($table);
    }

    /**
     * Create Update Query
     *
     * @param $table
     * @return QueryUpdate
     */
    public function update($table)
    {
        return new QueryUpdate($table);
    }

    /**
     * Create Delete Query
     *
     * @param $table
     * @return QueryDelete
     */
    public function delete($table)
    {
        return new QueryDelete($table);
    }
}