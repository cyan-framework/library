<?php
namespace Cyan\Library;

/**
 * Class DatabaseTable
 * @package Cyan\Library
 * @since 1.0.0
 */
class DatabaseTable
{
    /**
     * @var Database
     * @since 1.0.0
     */
    protected $db;

    /**
     * @var DatabaseQuerySelect
     * @since 1.0.0
     */
    protected $query;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $table;

    /**
     * @var Database|Result|Row
     * @since 1.0.0
     */
    protected $parent;

    /**
     * Constructor
     * Use $db->createResult( $parent, $name ) instead
     *
     * @param Database|DatabaseTable $parent
     * @param string $name
     *
     * @since 1.0.0
     */
    public function __construct( $parent, $name )
    {
        if ( $parent instanceof Database ) {
            // basic result

            $this->db = $parent;
            $this->table = $this->db->schema()->getAlias( $name );
            $this->query = DatabaseQuery::getInstance()->from($this->table);
        } else {
            // result referenced to parent
            $this->parent = $parent;
            $this->db = $parent->getDatabase();
            $this->query = $parent->getDatabaseQuery();

            // determine type of reference based on conventions and user hints
            $this->table = $this->db->schema()->isAlias( $name ) ? $this->db->schema()->getTable($name) : $name ;

            if ( $parent->getTable() == $this->table ) {
                $this->key = $this->db->schema()->getPrimary( $this->getTable() );
                $this->parentKey = $this->db->schema()->getReference( $parent->getTable(), $name );
            } else {
                $this->key = $this->db->schema()->getBackReference( $parent->getTable(), $name );
                $this->parentKey = $this->db->schema()->getPrimary( $parent->getTable() );
            }
        }
    }

    /**
     * @return Database
     *
     * @since 1.0.0
     */
    public function getDatabase()
    {
        return $this->db;
    }

    /**
     * @return DatabaseQuerySelect
     *
     * @since 1.0.0
     */
    public function getDatabaseQuery()
    {
        return $this->query;
    }

    /**
     * @return mixed
     *
     * @since 1.0.0
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return Database|Result|Row
     *
     * @since 1.0.0
     */
    public function getParent()
    {
        return $this->parent;
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

    /**
     * Create a back reference
     *
     * @param $key
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function via($key)
    {
        if ($this->parent instanceof DatabaseTable) {
            $this->db->schema()->setReference($this->parent->getTable(), $this->table, $key);
        }

        return $this;
    }

    /**
     * Single Row
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function fetch()
    {
        $sth = $this->db->prepare((string)$this->query);
        $sth->execute($this->query->getParameters());
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Array Rows
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function fetchAll()
    {
        $sth = $this->db->prepare($this->query);
        $sth->execute($this->query->getParameters());
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Call methods from query or database
     *
     * @param $method_name
     * @param $arguments
     *
     * @return DatabaseTable
     *
     * @since 1.0.0
     * @throws \BadMethodCallException
     */
    public function __call($method_name, $arguments)
    {
        if (method_exists($this->query, $method_name)) {
            $total_args = count($arguments);
            switch ($total_args)
            {
                case 0:
                    $this->query->$method_name();
                    break;
                case 1:
                    $this->query->$method_name($arguments[0]);
                    break;
                case 2:
                    $this->query->$method_name($arguments[0], $arguments[1]);
                    break;
                case 3:
                    $this->query->$method_name($arguments[0], $arguments[1], $arguments[2]);
                    break;
                default:
                    call_user_func_array([$this->query, $method_name], $arguments);
                    break;
            }

            return $this;
        }

        throw new \BadMethodCallException(sprintf('Invalid method "%s" in class %s',$method_name, get_class($this)));
    }
}