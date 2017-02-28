<?php
namespace Cyan\Framework;

/**
 * Class Database
 * @package Cyan\Framework
 * @since 1.0.0
 */
class Database
{
    use DatabaseTraitPdo;

    /**
     * Array Config
     *
     * @var array
     * @since 1.0.0
     */
    private $config = [];

    /**
     * set Database Config
     *
     * @param array $config
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Return Database Config
     *
     * @param $scope Database::$READ or Database::$WRITE
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getConfig($scope = null)
    {
        if (!empty($scope) && $scope != self::$READ && $scope != self::$WRITE) {
            $scope = null;
        }

        return !empty($scope) ? $this->config[$scope] : $this->config;
    }

    /**
     * @return DatabaseSchema
     *
     * @since 1.0.0
     */
    public function schema()
    {
        return DatabaseSchema::getInstance();
    }

    /**
     * @return $this
     *
     * @since 1.0.0
     * @throws DatabaseException
     */
    public function connect()
    {
        if (empty($this->config)) {
            throw new DatabaseException('Config must be not empty');
        }

        $pdo = [];
        if (!isset($this->config[self::$READ]) && !isset($this->config[self::$WRITE])) {
            $this->config = [
                self::$READ => $this->config,
                self::$WRITE => $this->config
            ];
            $pdo_write_connection = $pdo_read_connection = $this->createPdo($this->config[self::$READ]);
        } else {
            $read_config = isset($this->config[self::$READ]) ? $this->config[self::$READ] : [];
            if (isset($this->config[self::$READ])) {
                unset($this->config[self::$READ]);
            }

            $write_config = isset($this->config[self::$WRITE]) ? $this->config[self::$WRITE] : [];
            if (isset($this->config[self::$WRITE])) {
                unset($this->config[self::$WRITE]);
            }

            $default_config = $this->config;
            $this->config = [
                self::$READ => array_merge($default_config, $read_config),
                self::$WRITE => array_merge($default_config, $write_config)
            ];
            $pdo_read_connection = $this->createPdo($this->config[self::$READ]);
            $pdo_write_connection = $this->createPdo($this->config[self::$WRITE]);
        }

        $this->pdo = [
            self::$READ => $pdo_read_connection,
            self::$WRITE => $pdo_write_connection
        ];

        return $this;
    }

    /**
     * create a query
     *
     * @return DatabaseQuery
     *
     * @since 1.0.0
     */
    public function getDatabaseQuery()
    {
        return DatabaseQuery::getInstance();
    }

    /**
     * @param $name
     *
     * @return DatabaseTable
     *
     * @since 1.0.0
     */
    public function table($name)
    {
        return new DatabaseTable($this,$name);
    }
}