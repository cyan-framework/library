<?php
namespace Cyan\Library;

/**
 * Class Filter
 * @package Cyan\Library
 */
class Filter
{
    /**
     * List of Regex Filters
     *
     * @var array
     */
    protected $filters = array();

    /**
     * Self Instance
     *
     * @var self
     */
    protected static $instance;

    /**
     * Singleton instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Filter a value
     *
     * @param $filter
     * @param null $value
     * @return null|string
     * @throws \RuntimeException
     */
    public function filter($filter, $value = null)
    {
        // skip condition
        if (!is_string($value)) {
            return $value;
        }

        if (array_key_exists($filter, $this->filters)) {
            $regex = $this->filters[$filter];
            preg_match_all($regex,$value, $output);
            if (!empty($output)) {
                $value = is_string($output) ? $output : is_array($output) ? implode($output[0]) : $value ;
            } else {
                $value = null;
            }
        } else {
            throw new FilterException(sprintf('Filter "%s" not found in %s',$filter,get_class($this)));
        }

        return $value;
    }

    /**
     * Filter Array List from a $handle source
     *
     * @param array $filters
     * @param $handle
     * @return array
     */
    public function getArray(array $filters, array $handle)
    {
        $data = [];
        foreach ($filters as $key => $filter) {
            if (isset($handle[$key])) {
                $data[$key] = $this->filter($filter, $handle[$key]);
            }
        }

        return $data;
    }

    /**
     * Map single Filter
     *
     * @param $identifier
     * @param $regex
     * @return $this
     */
    public function mapFilter($identifier, $regex)
    {
        $this->filters[$identifier] = $regex;

        return $this;
    }

    /**
     * Map a collection of filters
     *
     * @param array $filters
     * @return $this
     */
    public function mapFilters(array $filters = array())
    {
        foreach ($filters as $filter => $regex)
        {
            $this->mapFilter($filter, $regex);
        }

        return $this;
    }

    /**
     * List of filters keys
     *
     * @return array
     */
    public function getFiltersList()
    {
        return array_keys($this->filters);
    }
}