<?php
namespace Cyan\Library;

/**
 * Class Database
 * @package Cyan\Library
 */
class Database2
{
    use Database2TraitPdo, Database2TraitSchema, Database2TraitSql;

    /**
     * Connection Name
     *
     * @var string
     */
    protected $_name;

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
            throw new Database2Exception('Config must be not empty');
        }

        $pdo = [];
        if (!isset($this->_config['read']) && !isset($this->_config['write'])) {
            $this->_config = [
                'read' => $this->_config,
                'write' => $this->_config
            ];
            $pdoWriteConnection = $pdoReadConnection = $this->createPdo($this->_config['read']);
        } else {
            $readConfig = isset($this->_config['read']) ? $this->_config['read'] : [];
            if (isset($this->_config['read'])) {
                unset($this->_config['read']);
            }

            $writeConfig = isset($this->_config['write']) ? $this->_config['write'] : [];
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

        $this->pdo = [
            'read' => $pdoReadConnection,
            'write' => $pdoWriteConnection
        ];
        $config = $this->_config;
        $this->setRewrite(function( $table ) use ($config) {
            $tbl_prefix = isset($config['read']['prefix']) ? $config['read']['prefix'] : '' ;
            return $tbl_prefix.str_replace('#__','',$table);
        });

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
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns a result for table $name.
     * If $id is given, return the row with that id.
     *
     * @param $name
     * @param int|null $id
     * @return Database2Result|Database2Row|null
     */
    public function table( $name, $id = null )
    {
        // ignore List suffix
        $name = preg_replace( '/List$/', '', $name );

        if ( $id !== null ) {
            $result = $this->createResult( $this, $name );

            if ( !is_array( $id ) ) {
                $table = $this->getAlias( $name );
                $primary = $this->getPrimary( $table );
                $id = array( $primary => $id );
            }

            return $result->where( $id )->fetch();
        }

        return $this->createResult( $this, $name );
    }

    /**
     * Create a row from given properties.
     * Optionally bind it to the given result.
     *
     * @param string $name
     * @param array $properties
     * @param Result|null $result
     * @return Row
     */
    public function createRow( $name, $properties = array(), $result = null )
    {
        return new Database2Row( $this, $name, $properties, $result );
    }

    /**
     * Create a result bound to $parent using table or association $name.
     * $parent may be the database, a result, or a row
     *
     * @param Database|Result|Row $parent
     * @param string $name
     * @return Result
     */
    public function createResult( $parent, $name )
    {
        return new Database2Result( $parent, $name );
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public function __call( $name, $args )
    {
        array_unshift( $args, $name );
        return call_user_func_array( array( $this, 'table' ), $args );
    }
}