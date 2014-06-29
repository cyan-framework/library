<?php
namespace Cyan\Library;

class Data
{
    /**
     * Singleton Instance
     *
     * @var self
     */
    private static $instance;

    /**
     * @var \ArrayObject
     */
    protected $_data;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->_data = new \ArrayObject();
    }

    /**
     * Singleton Instance
     *
     * @param array $config
     * @return self
     */
    public static function getInstance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Define Data
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     * Return All Data
     *
     * @return \ArrayObject
     */
    public function all()
    {
        return $this->_data;
    }

    /**
     * Search by specific condition
     *
     * @param $condition
     */
    public function findBy(array $condition)
    {
        $results = new \ArrayObject();
        foreach ($this->_data as $key => $object) {
            $asserts = 0;
            foreach ($condition as $field => $expected_value) {
                if (isset($object[$field]) && $object[$field] == $expected_value) {
                    $asserts++;
                }
            }

            if (count($condition) === $asserts) {
                $results[] = $object;
            }
        }

        return (count($results) == 1) ? $results[0] : $results;
    }
}