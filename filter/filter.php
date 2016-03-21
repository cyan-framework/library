<?php
namespace Cyan\Framework;

/**
 * Class Filter
 * @package Cyan\Framework
 * @since 1.0.0
 *
 * @method Filter getInstance
 */
class Filter
{
    use TraitSingleton;

    /**
     * List of Regex Filters
     *
     * @var array
     * @since 1.0.0
     */
    protected $filters = [];

    /**
     * List of Callback Filters
     *
     * @var array
     * @since 1.0.0
     */
    protected $callback = [];

    /**
     * Filter constructor.
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->filters = json_decode(file_get_contents(__DIR__.'/filters.json'),true);
    }

    /**
     * Filter a value
     *
     * @param $filter
     * @param null $value
     *
     * @return null|string
     *
     * @throws \RuntimeException
     *
     * @since 1.0.0
     */
    public function filter($filter, $value = null, $default_value = null)
    {
        // skip condition
        if (!is_string($value) || $filter == 'cyan_raw') {
            return $value;
        }

        if (array_key_exists($filter, $this->filters)) {
            $regex = $this->filters[$filter];
            preg_match_all($regex,$value, $output);
            if (!empty($output)) {
                $value = is_string($output) ? $output : is_array($output) ? implode($output[0]) : $value ;
            } else {
                $value = $default_value;
            }
        } else {
            throw new FilterException(sprintf('Filter "%s" not found in %s',$filter,get_class($this)));
        }

        $value = $this->applyCallback($filter, $value);

        return $value;
    }

    /**
     * Apply a callback filter
     *
     * @param $filter
     * @param $value
     *
     * @return string|mixed
     *
     * @since 1.0.0
     */
    public function applyCallback($filter, $value)
    {
        return isset($this->callback[$filter]) ? $this->callback[$filter]($value) : $value;
    }

    /**
     * Add a Closure do filter
     *
     * @param $filter
     * @param \Closure $callback
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function addFilterCallback($filter, \Closure $callback)
    {
        $this->callback[$filter] = $callback;

        return $this;
    }

    /**
     * Filter Array List from a $handle source
     *
     * @param array $filters
     * @param $handle
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getArray(array $filters, array $handle)
    {
        foreach ($filters as $key => $filter) {
            if (isset($handle[$key])) {
                $handle[$key] = $this->filter($filter, $handle[$key]);
            }
        }

        return $handle;
    }

    /**
     * Map single Filter
     *
     * @param string $identifier
     * @param string $regex
     *
     * @return $this
     *
     * @since 1.0.0
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
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function mapFilters(array $filters = [])
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
     *
     * @since 1.0.0
     */
    public function getFiltersList()
    {
        return array_keys($this->filters);
    }

    /**
     * Get regex from filter
     *
     * @param $filter
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getRegex($filter)
    {
        return $this->filters[$filter];
    }
}