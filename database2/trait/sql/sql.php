<?php
namespace Cyan\Library;

trait Database2TraitSql
{
    /** @var null|callable */
    protected $queryCallback;

    /** @var string */
    protected $identifierDelimiter = "`";

    /**
     * Select rows from a table
     *
     * @param string $table
     * @param mixed $exprs
     * @param array $where
     * @param array $orderBy
     * @param int|null $limitCount
     * @param int|null $limitOffset
     * @param array $params
     * @return \PDOStatement
     */
    function select( $table, $options = array() ) {

        $options = array_merge( array(

            'expr' => null,
            'where' => array(),
            'orderBy' => array(),
            'limitCount' => null,
            'limitOffset' => null,
            'params' => array()

        ), $options );

        $query = "SELECT ";

        if ( empty( $options[ 'expr' ] ) ) {

            $query .= "*";

        } else if ( is_array( $options[ 'expr' ] ) ) {

            $query .= implode( ", ", $options[ 'expr' ] );

        } else {

            $query .= $options[ 'expr' ];

        }

        $table = $this->rewriteTable( $table );
        $query .= " FROM " . $this->quoteIdentifier( $table );

        $query .= $this->getSuffix( $options[ 'where' ], $options[ 'orderBy' ], $options[ 'limitCount' ], $options[ 'limitOffset' ] );

        $this->onQuery( $query, $options[ 'params' ] );

        $statement = $this->prepare( $query );
        $statement->setFetchMode( \PDO::FETCH_ASSOC );
        $statement->execute( $options[ 'params' ] );

        return $statement;

    }

    /**
     * Insert one ore more rows into a table
     *
     * The $method parameter selects one of the following insert methods:
     *
     * "prepared": Prepare a query and execute it once per row using bound params
     *             Does not support Literals in row data (PDO limitation)
     *
     * "batch":    Create a single query mit multiple value lists
     *             Supports Literals, but not supported everywhere
     *
     * default:    Execute one INSERT per row
     *             Supports Literals, supported everywhere, slow for many rows
     *
     * @param string $table
     * @param array $rows
     * @param string|null $method
     * @return \PDOStatement|null
     */
    function insert( $table, $rows, $method = null ) {

        if ( empty( $rows ) ) return;
        if ( !isset( $rows[ 0 ] ) ) $rows = array( $rows );

        if ( $method === 'prepared' ) {

            return $this->insertPrepared( $table, $rows );

        } else if ( $method === 'batch' ) {

            return $this->insertBatch( $table, $rows );

        } else {

            return $this->insertDefault( $table, $rows );

        }

    }

    /**
     * Insert rows using a prepared query
     *
     * @param string $table
     * @param array $rows
     * @return \PDOStatement|null
     */
    protected function insertPrepared( $table, $rows ) {

        $columns = $this->getColumns( $rows );
        if ( empty( $columns ) ) return;

        $query = $this->insertHead( $table, $columns );
        $query .= "( ?" . str_repeat( ", ?", count( $columns ) - 1 ) . " )";

        $statement = $this->prepare( $query );

        foreach ( $rows as $row ) {

            $values = array();

            foreach ( $columns as $column ) {

                $value = (string) $this->format( @$row[ $column ] );
                $values[] = $value;

            }

            $this->onQuery( $query, $values );

            $statement->execute( $values );

        }

        return $statement;

    }

    /**
     * Insert rows using a single batch query
     *
     * @param string $table
     * @param array $rows
     * @return \PDOStatement|null
     */
    protected function insertBatch( $table, $rows ) {

        $columns = $this->getColumns( $rows );
        if ( empty( $columns ) ) return;

        $query = $this->insertHead( $table, $columns );
        $lists = $this->valueLists( $rows, $columns );
        $query .= implode( ", ", $lists );

        $this->onQuery( $query );

        $statement = $this->prepare( $query );
        $statement->execute();

        return $statement;

    }

    /**
     * Insert rows using one query per row
     *
     * @param string $table
     * @param array $rows
     * @return \PDOStatement|null
     */
    protected function insertDefault( $table, $rows ) {

        $columns = $this->getColumns( $rows );
        if ( empty( $columns ) ) return;

        $query = $this->insertHead( $table, $columns );
        $lists = $this->valueLists( $rows, $columns );

        foreach ( $lists as $list ) {

            $singleQuery = $query . $list;

            $this->onQuery( $singleQuery );

            $statement = $this->prepare( $singleQuery );
            $statement->execute();

        }

        return $statement; // last statement is returned

    }

    /**
     * Build head of INSERT query (without values)
     *
     * @param string $table
     * @param array $columns
     * @return string
     */
    protected function insertHead( $table, $columns ) {

        $quotedColumns = array_map( array( $this, 'quoteIdentifier' ), $columns );
        $table = $this->rewriteTable( $table );
        $query = "INSERT INTO " . $this->quoteIdentifier( $table );
        $query .= " ( " . implode( ", ", $quotedColumns ) . " ) VALUES ";

        return $query;

    }

    /**
     * Get list of all columns used in the given rows
     *
     * @param array $rows
     * @return array
     */
    protected function getColumns( $rows ) {

        $columns = array();

        foreach ( $rows as $row ) {

            foreach ( $row as $column => $value ) {

                $columns[ $column ] = true;

            }

        }

        return array_keys( $columns );

    }

    /**
     * Build lists of quoted values for INSERT
     *
     * @param array $rows
     * @param array $columns
     * @return array
     */
    protected function valueLists( $rows, $columns ) {

        $lists = array();

        foreach ( $rows as $row ) {

            $values = array();

            foreach ( $columns as $column ) {
                $values[] = $this->quote( @$row[ $column ] );
            }

            $lists[] = "( " . implode( ", ", $values ) . " )";

        }

        return $lists;

    }

    /**
     * Execute update query and return statement
     *
     * UPDATE $table SET $data [WHERE $where]
     *
     * @param string $table
     * @param array $data
     * @param array $where
     * @param array $params
     * @return null|\PDOStatement
     */
    function update( $table, $data, $where = array(), $params = array() ) {

        if ( empty( $data ) ) return;

        $set = array();

        foreach ( $data as $column => $value ) {

            $set[] = $this->quoteIdentifier( $column ) . " = " . $this->quote( $value );

        }

        if ( !is_array( $where ) ) $where = array( $where );
        if ( !is_array( $params ) ) $params = array_slice( func_get_args(), 3 );

        $table = $this->rewriteTable( $table );
        $query = "UPDATE " . $this->quoteIdentifier( $table );
        $query .= " SET " . implode( ", ", $set );
        $query .= $this->getSuffix( $where );

        $this->onQuery( $query, $params );

        $statement = $this->prepare( $query );
        $statement->execute( $params );

        return $statement;

    }

    /**
     * Execute delete query and return statement
     *
     * DELETE FROM $table [WHERE $where]
     *
     * @param string $table
     * @param array $where
     * @param array $params
     * @return \PDOStatement
     */
    function delete( $table, $where = array(), $params = array() ) {

        if ( !is_array( $where ) ) $where = array( $where );
        if ( !is_array( $params ) ) $params = array_slice( func_get_args(), 2 );

        $table = $this->rewriteTable( $table );
        $query = "DELETE FROM " . $this->quoteIdentifier( $table );
        $query .= $this->getSuffix( $where );

        $this->onQuery( $query, $params );

        $statement = $this->prepare( $query );
        $statement->execute( $params );

        return $statement;

    }

    // SQL utility

    /**
     * Return WHERE/LIMIT/ORDER suffix for queries
     *
     * @param array $where
     * @param array $orderBy
     * @param int|null $limitCount
     * @param int|null $limitOffset
     * @return string
     */
    function getSuffix( $where, $orderBy = array(), $limitCount = null, $limitOffset = null ) {

        $suffix = "";

        if ( !empty( $where ) ) {

            $suffix .= " WHERE " . implode( " AND ", $where );

        }

        if ( !empty( $orderBy ) ) {

            $suffix .= " ORDER BY " . implode( ", ", $orderBy );

        }

        if ( isset( $limitCount ) ) {

            $suffix .= " LIMIT " . intval( $limitCount );

            if ( isset( $limitOffset ) ) {

                $suffix .= " OFFSET " . intval( $limitOffset );

            }

        }

        return $suffix;

    }

    /**
     * Build an SQL condition expressing that "$column is $value",
     * or "$column is in $value" if $value is an array. Handles null
     * and literals like new Literal( "NOW()" ) correctly.
     *
     * @param string $column
     * @param string|array $value
     * @param bool $not
     * @return string
     */
    function is( $column, $value, $not = false ) {

        $bang = $not ? "!" : "";
        $or = $not ? " AND " : " OR ";
        $novalue = $not ? "1=1" : "0=1";
        $not = $not ? " NOT" : "";

        // always treat value as array
        if ( !is_array( $value ) ) {

            $value = array( $value );

        }

        // always quote column identifier
        $column = $this->quoteIdentifier( $column );

        if ( count( $value ) === 1 ) {

            // use single column comparison if count is 1

            $value = $value[ 0 ];

            if ( $value === null ) {

                return $column . " IS" . $not . " NULL";

            } else {

                return $column . " " . $bang . "= " . $this->quote( $value );
            }

        } else if ( count( $value ) > 1 ) {

            // if we have multiple values, use IN clause

            $values = array();
            $null = false;

            foreach ( $value as $v ) {

                if ( $v === null ) {

                    $null = true;

                } else {

                    $values[] = $this->quote( $v );

                }

            }

            $clauses = array();

            if ( !empty( $values ) ) {

                $clauses[] = $column . $not . " IN ( " . implode( ", ", $values ) . " )";

            }

            if ( $null ) {

                $clauses[] = $column . " IS" . $not . " NULL";

            }

            return implode( $or, $clauses );

        }

        return $novalue;

    }

    /**
     * Build an SQL condition expressing that "$column is not $value"
     * or "$column is not in $value" if $value is an array. Handles null
     * and literals like new Literal( "NOW()" ) correctly.
     *
     * @param string $column
     * @param string|array $value
     * @return string
     */
    function isNot( $column, $value ) {

        return $this->is( $column, $value, true );

    }

    /**
     * Quote a value for SQL
     *
     * @param mixed $value
     * @return string
     */
    public function quote( $value ) {

        $value = $this->format( $value );

        if ( $value === null ) {

            return "NULL";

        }

        if ( $value === false ) {

            return "'0'";

        }

        if ( $value === true ) {

            return "'1'";

        }

        if ( is_int( $value ) ) {

            return "'" . ( (string) $value ) . "'";

        }

        if ( is_float( $value ) ) {

            return "'" . sprintf( "%F", $value ) . "'";

        }

        if ( $value instanceof Literal ) {

            return $value->value;

        }

        return $this->pdo['read']->quote( $value );

    }

    /**
     * Format a value for SQL, e.g. DateTime objects
     *
     * @param mixed $value
     * @return string
     */
    function format( $value ) {

        if ( $value instanceof \DateTime ) {

            return $value->format( "Y-m-d H:i:s" );

        }

        return $value;

    }

    /**
     * Quote identifier
     *
     * @param string $identifier
     * @return string
     */
    function quoteIdentifier( $identifier ) {

        $delimiter = $this->identifierDelimiter;

        if ( empty( $delimiter ) ) return $identifier;

        $identifier = explode( ".", $identifier );

        $identifier = array_map(
            function( $part ) use ( $delimiter ) { return $delimiter . str_replace( $delimiter, $delimiter.$delimiter, $part ) . $delimiter; },
            $identifier
        );

        return implode( ".", $identifier );

    }

    /**
     * Create a SQL Literal
     *
     * @param string $value
     * @return Literal
     */
    function literal( $value ) {

        return new Literal( $value );

    }

    //

    /**
     * Calls the query callback, if any
     *
     * @param string $query
     * @param array $params
     */
    function onQuery( $query, $params = array() ) {

        if ( is_callable( $this->queryCallback ) ) {

            call_user_func( $this->queryCallback, $query, $params );

        }

    }

    /**
     * Set the query callback
     *
     * @param callable $callback
     * @return $this
     */
    function setQueryCallback( $callback ) {

        $this->queryCallback = $callback;

        return $this;

    }
}