<?php
namespace Cyan\Library;

/**
 * Class Database
 * @package Cyan\Library
 */
class Database
{
    /**
     * Connection Name
     *
     * @var string
     */
    protected $_name;

    /**
     * @var \PDO
     */
    protected $_pdo;

    /**
     * @var array|mixed|null
     */
    protected $_config = array();

    /**
     * @param $name
     */
    public function __construct($name)
    {
        $this->_name = $name;
    }

    /**
     * @return $this
     * @throws DatabaseException
     */
    public function connect()
    {
        if (empty($this->_config)) {
            throw new DatabaseException('Config must be not empty');
        }

        $pdo = [];
        if (!isset($this->_config['read']) && !isset($this->_config['write'])) {
            $this->_config = [
                'read' => $this->_config,
                'write' => $this->_config
            ];
            $pdoWriteConnection = $pdoReadConnection = $this->createPdo($this->_config['read']);
        } else {
            $readConfig = isset($this->_config['read']) ? $this->_config['read'] : [] ;
            if (isset($this->_config['read'])) {
                unset($this->_config['read']);
            }

            $writeConfig = isset($this->_config['write']) ? $this->_config['write'] : [] ;
            if (isset($this->_config['write'])) {
                unset($this->_config['write']);
            }

            $defaultConfig = $this->_config;
            $this->_config = [
                'read' => array_merge($defaultConfig, $readConfig),
                'write' => array_merge($defaultConfig, $writeConfig)
            ];
            $pdoReadConnection = $this->createPdo($this->_config['read']);
            $pdoWriteConnection = $this->createPdo($this->_config['write']);
        }

        $this->_pdo = [
            'read' => $pdoReadConnection,
            'write' => $pdoWriteConnection
        ];

        return $this;
    }

    /**
     * @return \PDO
     */
    private function createPdo($config)
    {
        $pdoDrivers = \PDO::getAvailableDrivers();
        if (empty($pdoDrivers))
        {
            throw new \PDOException ("PDO does not support any driver.");
        } elseif (!in_array($config['driver'],\PDO::getAvailableDrivers(),TRUE)) {
            throw new \PDOException(sprintf('Requested driver "%s" are not available. Available drivers: %s',$config['driver'], implode(', ',\PDO::getAvailableDrivers())));
        }

        switch ($config['driver']) {
            case 'mysql':
                $dsn = sprintf('%s:host=%s;dbname=%s',$config['driver'],$config['host'], $config['database']);
                $options = [];
                break;
            default:
                throw new DatabaseException(sprintf('Driver "%s" not found or is disabled.',$config['driver']));
                break;
        }

        $options = $config;
        return new \PDO($dsn, $config['username'], $config['password'], $options);
    }

    /**
     * @param $data
     * @return $this
     */
    public function setConfig($data)
    {
        $this->_config = $data;

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getConfig()
    {
        return Finder::getInstance()->getIdentifier('app:config.database', $this->_config);
    }

    /**
     * Return PDO
     *
     * @param null $action  String 'read' or 'write'
     * @return \PDO
     */
    public function getPdo($action = null)
    {
        return isset($this->pdo[$action]) ? $this->pdo[$action] : $this->_pdo;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Create Query Object
     *
     * @return Query
     */
    public function createQuery()
    {
        $this->isConnected('read');

        $query = new Query($this->_pdo);
        return $query;
    }

    /**
     * Verify and connect to a pdo
     *
     * @param $action
     * @return mixed
     */
    private function isConnected($action)
    {
        if (!isset($this->_pdo[$action])) {
            throw new \RuntimeException(sprintf('Pdo not support "%s" only support READ and WRITE action.'));
        }
        $pool = $this->_pdo[$action];

        if (!($pool instanceof \PDO)) {
            $this->connect();
        }

        return $pool;
    }

    /**
     * execute a query
     *
     * @param $sql
     * @param array $parameters
     * @param int $pdoFormat
     * @return mixed
     */
    public function execute($sql, array $parameters = array(), $pdoFormat = \PDO::FETCH_ASSOC)
    {
        //validate $sql
        if (!is_string($sql) && !($sql instanceof QueryBase)) {
            throw new \RuntimeException(sprintf('SQL must be an STRING or Query Object, ("%s") given.',gettype($sql)));
        }

        // if empty will get from query object
        if (($sql instanceof QueryBase)) {
            //validate parameters
            if (!empty($parameters) && count(array_diff($parameters, $sql->getParameters()))) {
                $parameters = $sql->getParameters();
            }
        }

        $statement = strtolower(strtok(trim($sql), " "));

        $action =  ($statement == 'select') ? 'read' : 'write' ;
        $pdo = $this->isConnected($action);

        $sth = $pdo->prepare($sql);

        $return = $sth->execute($parameters);

        if ($action == 'read') {
            return $sth->fetchAll($pdoFormat);
        } else {
            return $return;
        }
    }

    /**
     * Create
     *
     * @param $table
     * @param $data
     * @return array
     */
    public function insert($table, $data)
    {
        $this->isConnected('write');

        if (empty($data)) {
            return $data;
        }

        $sql = $this->createQuery()->insertInto($table, $data);
        $return = $sql->execute();

        return $return;
    }

    /**
     * Find Content with according configuration
     *
     * @param $table
     * @param array $condition
     * @param string $fields
     * @return array
     */
    function find($table, array $condition = array(), $fields = '*')
    {
        $this->isConnected('read');

        $sql = $this->createQuery()->from($table)->where($condition)->select($fields);

        $sth = $this->_pdo['read']->prepare($sql->getQuery());
        $return = $sth->execute($sql->getParameters());

        if (!$return) {
            return array();
        }

        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Remove
     *
     * @param $table
     * @param $condition
     * @param $primary_key
     * @return bool
     */
    public function remove($table, $condition, $primary_key = null)
    {
        $this->isConnected('write');

        $sql = $this->createQuery()->deleteFrom($table, $primary_key)->where($condition);
        $return = $sql->execute();

        return $return;
    }

    /**
     * Update Table
     *
     * @param $table
     * @param $fields
     * @param $condition
     * @param $primary_key
     * @return bool
     */
    public function update($table, $fields, $condition, $primary_key = null)
    {
        $this->isConnected('write');

        $sql = $this->createQuery()->update($table, $fields, $primary_key)->where($condition);
        $return = $sql->execute();

        return $return;
    }
}