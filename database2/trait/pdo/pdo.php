<?php
namespace Cyan\Library;

/**
 * Class Database2TraitPdo
 * @package Cyan\Library
 */
trait Database2TraitPdo
{
    static $READ = 'read';
    static $WRITE = 'write';

    /**
     * PDO Statement
     *
     * @var \PDOStatement
     */
    protected $sth;

    /**
     * @var \PDO
     */
    protected $pdo = [];

    /**
     * @return \PDO
     */
    protected function createPdo($config)
    {
        $pdoDrivers = \PDO::getAvailableDrivers();
        if (empty($pdoDrivers))
        {
            throw new \Database2Exception("PDO does not support any driver.");
        } elseif (!in_array($config['driver'],\PDO::getAvailableDrivers(),TRUE)) {
            throw new \Database2Exception(sprintf('Requested driver "%s" are not available. Available drivers: %s',$config['driver'], implode(', ',\PDO::getAvailableDrivers())));
        }

        switch ($config['driver']) {
            case 'sqlsrv':
                $dsn = sprintf('%s:Server=%s;Database=%s',$config['driver'],$config['host'], $config['database']);
                $options = [];
                break;
            case 'pgsql':
            case 'mysql':
                $dsn = sprintf('%s:host=%s;dbname=%s',$config['driver'],$config['host'], $config['database']);
                $options = [];
                break;
            default:
                throw new Database2Exception(sprintf('Driver "%s" not found or is disabled.',$config['driver']));
                break;
        }

        $options = $config;

        $pdo = new \PDO($dsn, $config['username'], $config['password'], $options);
        $pdo->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
        return $pdo;
    }

    /**
     * Return PDO
     *
     * @param null $action  String 'read' or 'write'
     * @return \PDO
     */
    public function getPdo($action = null)
    {
        return isset($this->pdo[$action]) ? $this->pdo[$action] : $this->pdo;
    }

    /**
     * Verify and connect to a pdo
     *
     * @param $action
     * @return mixed
     */
    private function isConnected($action)
    {
        if (!isset($this->pdo[$action])) {
            throw new Database2Exception(sprintf('Pdo not support "%s" only support READ and WRITE action.', $action));
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
     * @param string $action
     * @return \PDOStatement
     */
    public function prepare($query)
    {
        $statement = strtolower(strtok(trim($query), " "));
        $action =  ($statement == 'select' || $statement == 'show') ? 'read' : 'write' ;
        $pdo = $this->isConnected($action);
        return $pdo->prepare( $query );
    }

    /**
     * Return last inserted id
     *
     * @param string|null $sequence
     * @return string
     */
    public function lastInsertId($sequence = null)
    {
        return $this->getPdo(self::$WRITE)->lastInsertId($sequence);
    }

    /**
     * Begin a transaction
     *
     * @return bool
     */
    public function begin()
    {
        return $this->getPdo(self::$WRITE)->beginTransaction();
    }

    /**
     * Commit changes of transaction
     *
     * @return bool
     */
    public function commit()
    {
        return $this->getPdo(self::$WRITE)->commit();
    }

    /**
     * Rollback any changes during transaction
     *
     * @return bool
     */
    public function rollback()
    {
        return $this->getPdo(self::$WRITE)->rollBack();
    }

    /**
     * Return PDO error
     *
     * @return array
     */
    public function getError()
    {
        return array_filter($this->sth->errorInfo());
    }
}