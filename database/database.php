<?php
namespace Cyan\Library;

/**
 * Class Database
 * @package Cyan\Library
 */
class Database
{
    /**
     * PDO Statement
     *
     * @var \PDOStatement
     */
    private $sth;

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
    protected $_config = [];

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
        return isset($this->_pdo[$action]) ? $this->_pdo[$action] : $this->_pdo;
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
            throw new \RuntimeException(sprintf('Pdo not support "%s" only support READ and WRITE action.', $action));
        }
        $pool = $this->_pdo[$action];

        if (!($pool instanceof \PDO)) {
            $this->connect();
        }

        return $pool;
    }

    /**
     * @param $field
     * @param $table
     * @param string $where
     * @param array $parameters
     * @return mixed
     */
    public function count($field, $table, $where = '', array $parameters = [])
    {
        $pdo = $this->isConnected('read');

        $sql = $this->createQuery()->from($table)->count($field);
        if (!empty($where)) {
            $sql->where($where);
        }
        $sql->parameters($parameters);

        $this->_config['prefix'] = isset($this->_config['prefix']) ? $this->_config['prefix'] : '' ;
        $this->sth = $pdo->prepare((string)str_replace('#__',$this->_config['prefix'],$sql));
        $return = $this->sth->execute($sql->getParameters());

        return $this->sth->fetchColumn();
    }

    /**
     * execute a query
     *
     * @param $sql
     * @param array $parameters
     * @param int $pdoFormat
     * @return mixed
     */
    public function execute($sql, array $parameters = [], $pdoFormat = \PDO::FETCH_ASSOC)
    {
        //validate $sql
        if (!is_string($sql) && !($sql instanceof QueryBase)) {
            throw new \RuntimeException(sprintf('SQL must be an STRING or Query Object, ("%s") given.',gettype($sql)));
        }

        // if empty will get from query object
        if (($sql instanceof QueryBase)) {
            //validate parameters
            if (empty($parameters)) {
                $parameters = $sql->getParameters();
            }
        }

        $statement = strtolower(strtok(trim($sql), " "));
        $action =  ($statement == 'select' || $statement == 'show') ? 'read' : 'write' ;
        $pdo = $this->isConnected($action);

        $this->_config['prefix'] = isset($this->_config['prefix']) ? $this->_config['prefix'] : '' ;

        $sqlQuery = (string)str_replace('#__',$this->_config['prefix'],$sql);
        $this->sth = $pdo->prepare($sqlQuery);

        $return = $this->sth->execute($parameters);

        if ($action == 'read') {
            return $this->sth->fetchAll($pdoFormat);
        } else {
            return $return;
        }
    }

    /**
     * Insert table
     *
     * @param $table
     * @param array $fields
     * @param array $parameters
     * @return array
     */
    public function insert($table, array $fields, array $parameters)
    {
        $pdo = $this->isConnected('write');

        $sql = $this->createQuery()->insert($table)->columns(array_keys($fields))->values(array_values($fields))->parameters($parameters);

        $this->_config['prefix'] = isset($this->_config['prefix']) ? $this->_config['prefix'] : '' ;

        $insertSQL = str_replace('#__',$this->_config['prefix'],(string)$sql);
        $this->sth = $pdo->prepare($insertSQL);
        $return = $this->sth->execute($sql->getParameters());

        return $return;
    }

    /**
     * Return last insert id
     *
     * @return mixed
     */
    public function lastInsertID()
    {
        $pdo = $this->isConnected('write');

        $sql = 'SELECT LAST_INSERT_ID()';
        $this->sth = $pdo->prepare($sql);
        $this->sth->execute();
        $lastId = $this->sth->fetch(\PDO::FETCH_NUM);

        return $lastId[0];
    }

    /**
     * Find Content with according configuration
     *
     * @param $table
     * @param array $condition
     * @param string $fields
     * @return array
     */
    function find($table, $where, $fields = '*', array $parameters = [])
    {
        $pdo = $this->isConnected('read');

        $sql = $this->createQuery()->from($table)->where($where)->select($fields)->parameters($parameters);

        $this->_config['prefix'] = isset($this->_config['prefix']) ? $this->_config['prefix'] : '' ;

        $sqlString = (string)str_replace('#__',$this->_config['prefix'],$sql);
        $this->sth = $pdo->prepare($sqlString);

        $return = $this->sth->execute($sql->getParameters());

        if (!$return) {
            return [];
        }

        return $this->sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Find one item
     *
     * @param $table
     * @param $where
     * @param string $fields
     * @param array $parameters
     * @return array
     */
    public function findOne($table, $where, $fields = '*', array $parameters = [])
    {
        $pdo = $this->isConnected('read');

        $sql = $this->createQuery()->from($table)->where($where)->select($fields)->parameters($parameters);

        $this->_config['prefix'] = isset($this->_config['prefix']) ? $this->_config['prefix'] : '' ;

        $sqlString = (string)str_replace('#__',$this->_config['prefix'],$sql);
        $this->sth = $pdo->prepare($sqlString);

        $return = $this->sth->execute($sql->getParameters());

        if (!$return) {
            return [];
        }

        return $this->sth->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Remove
     *
     * @param $table
     * @param $condition
     * @return bool
     */
    public function remove($table, $condition, array $parameters = [])
    {
        $pdo = $this->isConnected('write');

        $sql = $this->createQuery()->delete($table)->where($condition)->parameters($parameters);

        $this->_config['prefix'] = isset($this->_config['prefix']) ? $this->_config['prefix'] : '' ;

        $this->sth = $pdo->prepare((string)str_replace('#__',$this->_config['prefix'],$sql));
        $return = $this->sth->execute($sql->getParameters());

        return $return;
    }

    /**
     * Update Table
     *
     * @param $table
     *
     * @param $fields
     *
     * @param $condition
     *
     * @param array $parameters
     *
     * @return mixed
     */
    public function update($table, $fields, $condition, array $parameters = [])
    {
        $pdo = $this->isConnected('write');

        $sql = $this->createQuery()->update($table)->set($fields)->where($condition)->parameters($parameters);

        $this->_config['prefix'] = isset($this->_config['prefix']) ? $this->_config['prefix'] : '' ;

        $this->sth = $pdo->prepare((string)str_replace('#__',$this->_config['prefix'],$sql));
        $return = $this->sth->execute($sql->getParameters());

        return $return;
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