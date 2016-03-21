<?php
namespace Cyan\Framework;

/**
 * Class DatabaseTraitPdo
 * @package Cyan\Framework
 * @since 1.0.0
 */
trait DatabaseTraitPdo
{
    /**
     * @var string
     * @since 1.0.0
     */
    static $READ = 'read';

    /**
     * @var string
     * @since 1.0.0
     */
    static $WRITE = 'write';

    /**
     * Create Database on connect
     *
     * @var bool
     * @since 1.0.0
     */
    protected $create_database = false;

    /**
     * Select Database on Connect
     *
     * @var bool
     * @since 1.0.0
     */
    protected $select_database = true;

    /**
     * PDO Statement
     *
     * @var \PDOStatement
     * @since 1.0.0
     */
    protected $sth;

    /**
     * @var \PDO
     * @since 1.0.0
     */
    protected $pdo = [];

    /**
     * Create database on PDO creation
     *
     * @param $create
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setCreateDatabase($create)
    {
        $this->create_database = $create;

        return $this;
    }

    /**
     * Select database on PDO creation
     *
     * @param $select
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setSelectDatabase($select)
    {
        $this->select_database = $select;

        return $this;
    }

    /**
     * @return \PDO
     *
     * @since 1.0.0
     */
    protected function createPdo($config)
    {
        $pdoDrivers = \PDO::getAvailableDrivers();
        if (empty($pdoDrivers))
        {
            throw new DatabaseException("PDO does not support any driver.");
        } elseif (!in_array($config['driver'],\PDO::getAvailableDrivers(),TRUE)) {
            throw new DatabaseException(sprintf('Requested driver "%s" are not available. Available drivers: %s',$config['driver'], implode(', ',\PDO::getAvailableDrivers())));
        }

        $selectDatabase = false;
        switch ($config['driver']) {
            case 'sqlsrv':
                $dsn = sprintf('%s:Server=%s;',$config['driver'],$config['host'], $config['database']);
                $options = [];
                break;
            case 'pgsql':
            case 'mysql':
                $dsn = sprintf('%s:host=%s;',$config['driver'],$config['host'], $config['database']);
                if (isset($config['charset'])) {
                    $dsn .= sprintf('charset=%s',$config['charset']);
                }
                $options = [];
                break;
            default:
                throw new DatabaseException(sprintf('Driver "%s" not found or is disabled.',$config['driver']));
                break;
        }

        $options = $config;

        $pdo = new \PDO($dsn, $config['username'], $config['password'], $options);
        $pdo->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
        if ($this->create_database) {
            $pdo->query("CREATE DATABASE IF NOT EXISTS {$config['database']}");
        }

        if ($this->select_database) {
            $pdo->query(sprintf('use `%s`',$config['database']));
        }

        return $pdo;
    }

    /**
     * Return PDO
     *
     * @param null $action  String 'read' or 'write'
     *
     * @return \PDO
     *
     * @since 1.0.0
     */
    public function getPdo($action = null)
    {
        return isset($this->pdo[$action]) ? $this->pdo[$action] : $this->pdo;
    }

    /**
     * Verify and connect to a pdo
     *
     * @param $action
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    private function isConnected($action)
    {
        if (!isset($this->pdo[$action])) {
            throw new DatabaseException(sprintf('Pdo not support "%s" only support READ and WRITE action.', $action));
        }
        $pool = $this->pdo[$action];

        if (!($pool instanceof \PDO)) {
            $this->connect();
        }

        return $pool;
    }

    /**
     * Prepare an SQL statement
     *
     * @param $query
     *
     * @return \PDO
     *
     * @since 1.0.0
     */
    public function prepare($query)
    {
        $statement = strtolower(strtok(trim($query), " "));
        $action =  ($statement == 'select' || $statement == 'show') ? self::$READ : self::$WRITE ;
        $pdo = $this->isConnected($action);
        return $pdo->prepare( $query );
    }

    /**
     * Return last inserted id
     *
     * @param string|null $sequence
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function lastInsertId($sequence = null)
    {
        return $this->getPdo(self::$WRITE)->lastInsertId($sequence);
    }

    /**
     * Begin a transaction
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function begin()
    {
        return $this->getPdo(self::$WRITE)->beginTransaction();
    }

    /**
     * Commit changes of transaction
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function commit()
    {
        return $this->getPdo(self::$WRITE)->commit();
    }

    /**
     * Rollback any changes during transaction
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function rollback()
    {
        return $this->getPdo(self::$WRITE)->rollBack();
    }

    /**
     * Return PDO error
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getError()
    {
        return array_filter($this->sth->errorInfo());
    }
}