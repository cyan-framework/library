<?php
namespace Cyan\Library;

class Connection
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
     * @throws ConnectionException
     */
    public function connect()
    {
        if (empty($this->_config)) {
            throw new ConnectionException('Config must be not empty');
        }

        $this->_pdo = new \PDO(sprintf('%s:host=%s;port=%s;dbname=%s',$this->_config['driver'],$this->_config['host'],$this->_config['port'],$this->_config['database']), $this->_config['user'], $this->_config['password']);

        $this->_config['prefix'] = isset($this->_config['prefix']) ? $this->_config['prefix'] : '' ;

        return $this;
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
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->_pdo;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param $table
     * @param $data
     * @return array
     */
    public function insert($table, $data)
    {
        if (!($this->_pdo instanceof \PDO)) {
            $this->connect();
        }

        if (empty($data)) {
            return $data;
        }

        $values = array_fill(0, count($data), '?');
        $bind = array_values($data);
        $sql = 'INSERT INTO ' . $table . ' (' . implode(', ',array_keys($data)) . ') ' . 'VALUES (' . implode(',',$values) . ')';

        $stmt = $this->_pdo->prepare($sql);
        $bool = $stmt->execute($bind);

        if ($bool) {
            $data['id'] = $this->_pdo->lastInsertId();
            $return = $data;
        } else {
            $return = array();
        }

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
        if (!($this->_pdo instanceof \PDO)) {
            $this->connect();
        }

        if (empty($fields)) {
            $fields = '*';
        }

        $sql_table = $this->_config['prefix'] . $table;

        $sql = sprintf('SELECT %s FROM %s',strval($fields), $sql_table);

        $where = array();
        $bindData = array();
        foreach ($condition as $key => $value) {
            $bindData[] = $value;
            $where[] = $key.' = ?';
        }

        if (count($where)) {
            $sql .= ' WHERE '.implode(' AND ',$where);
        }

        $sth = $this->_pdo->prepare($sql);
        $return = $sth->execute($bindData);

        if (!$return) {
            return array();
        }

        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $table
     * @param $condition
     * @return bool
     */
    public function remove($table, $condition)
    {
        if (!($this->_pdo instanceof \PDO)) {
            $this->connect();
        }

        $sql_table = $this->_config['prefix'] . $table;

        $sql = sprintf('DELETE FROM %s',$sql_table);

        $where = array();
        $bindData = array();
        foreach ($condition as $key => $value) {
            $bindData[] = $value;
            $where[] = $key.' = ?';
        }

        if (count($where)) {
            $sql .= ' WHERE '.implode(' AND ',$where);
        }

        $sth = $this->_pdo->prepare($sql);
        $return = $sth->execute($bindData);

        return $return;
    }

    /**
     * @param $table
     * @param $fields
     * @param $condition
     * @return bool
     */
    public function update($table, $fields, $condition)
    {
        if (!($this->_pdo instanceof \PDO)) {
            $this->connect();
        }

        $sql_table = $this->_config['prefix'] . $table;

        $sql = sprintf('UPDATE %s',$sql_table);

        $bindData = array();
        $set = array();
        foreach ($fields as $key => $value) {
            $bindData[] = $value;
            $set[] = $key.' = ?';
        }

        if (count($set)) {
            $sql .= ' SET '.implode(', ',$set);
        }

        $where = array();
        foreach ($condition as $key => $value) {
            $bindData[] = $value;
            $where[] = $key.' = ?';
        }

        if (count($where)) {
            $sql .= ' WHERE '.implode(' AND ',$where);
        }

        $sth = $this->_pdo->prepare($sql);
        $return = $sth->execute($bindData);

        return $return;
    }
}