<?php
namespace Cyan\Library;

/**
 * Class DataCollection
 * @package Cyan\Library
 * @since 1.0.0
 */
class DataCollection
{
    /**
     * @var string
     * @since 1.0.0
     */
    private $name = '';

    /**
     * First insert field keys
     *
     * @var array
     * @since 1.0.0
     */
    private $columns = [];

    /**
     * @var array
     * @since 1.0.0
     */
    private $options = [];

    /**
     * Items
     *
     * @var array
     * @since 1.0.0
     */
    private $items;

    /**
     * Last insert index
     *
     * @var int
     * @since 1.0.0
     */
    private $last_index = 0;

    /**
     * Total of items
     *
     * @var int
     * @since 1.0.0
     */
    private $total = 0;

    /**
     * DataCollection constructor.
     * @param $name
     * @param array $options
     *
     * @since 1.0.0
     */
    public function __construct($name, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;
        $this->items = [];
    }

    /**
     * Return Collection Name
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getCollectionName()
    {
        return $this->name;
    }

    /**
     * List all items
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Add a document to the collection.
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function insert()
    {
        $items = func_get_args();

        // insert only on first time the columns
        if (empty($this->columns) && empty($this->items)) {
            $item = count($items) > 1 ? $items[0] : $items;
            $this->columns = array_keys($item);
        }

        if (count($items) > 1) {
            $this->items = array_merge($this->items, $items);
        } else {
            $this->items[] = $items[0];
        }

        $this->last_index = max(array_keys($this->items));
        $this->total = count($this->items);

        return $this->last_index;
    }

    /**
     * Return all columns from document
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Update a item
     *
     * @param array $selector
     * @param array $modifier
     * @param array $options
     *
     * @since 1.0.0
     */
    public function update(array $selector, array $modifier, array $options)
    {

    }

    /**
     * @param array $selector
     *
     * @since 1.0.0
     */
    public function remove(array $selector)
    {

    }

    /**
     * Find in items
     *
     * @param array $selector
     * @param array $options
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function find(array $selector, array $options = [])
    {
        $result = [];

        if (empty($selector)) {
            $result = $this->items;
        } else {
            // define index of array based on item column
            $index_column = isset($options['index_column']) && is_string($options['index_column']) ? $options['index_column'] : '' ;
            // filter only specific fields
            $filter_fields = (isset($options['fields']) && is_array($options['fields']));

            $search = [];
            foreach ($selector as $operation => $data) {
                $condition = [];
                foreach ($data as $field => $value) {
                    $field_type = gettype($this->items[0][$field]);
                    switch ($field_type) {
                        case 'array':
                            $condition[$operation][] = sprintf('in_array("%s",$item'."['%s'])",$value,$field);
                            break;
                        case 'string':
                            $condition[$operation][] = sprintf('$item'."['%s'] = \"%s\"",$field, $value);
                            break;
                        default:
                            die(sprintf('Undefined operation for type "%s"', $field_type));
                            break;
                    }
                }
                $search[] = 'if ('.implode(' '.strtoupper($operation).' ', $condition[$operation]).') { $item_row = !$filter_fields ? $item : array_intersect_key($item, array_flip($options[\'fields\'])) ; if (!empty($item[$index_column])) { $result[$item[$index_column]] = $item_row; } else { $result[] = $item_row; } };';
            }

            $result = [];
            $if = implode($search);
            foreach ($this->items as $item) {
                eval($if);
            }
        }

        if (isset($options['fields']) && is_string($options['fields'])) {
            $result = array_column($result, $options['fields']);
        }

        return $this->filterByOptions($result, $options);
    }

    /**
     * filter items by options
     *
     * @param $items
     * @param $options
     *
     * @since 1.0.0
     */
    private function filterByOptions($items, $options)
    {
        if (empty($options)) {
            $this->total = count($items);
            return $items;
        }

        $this->total = count($items);

        if (!isset($options['limit']) && !isset($options['offset'])) {
            return $items;
        } else if (isset($options['limit']) && !isset($options['offset'])) {
            return array_slice(items, $options['limit']);
        } else {
            return array_slice(items, $options['limit'], $options['offset']);
        }
    }

    /**
     * Return total of items
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function count()
    {
        return $this->total;
    }
}