<?php
namespace Cyan\Library;

/**
 * Class QueryTraitOrder
 * @package Cyan\Library
 */
trait QueryTraitOrder
{
    /**
     * Add order to a query
     *
     * @param $condition
     * @return $this
     */
    public function orderBy($columns)
    {
        if (is_string($columns)) {
            $this->statements['order'][] = $columns;
        } elseif (is_array($columns)) {
            foreach ($columns as $field => $direction) {
                $this->statements['order'][] = $field.' '.$direction;
            }
        }

        return $this;
    }
}