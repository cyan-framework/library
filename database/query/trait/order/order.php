<?php
namespace Cyan\Library;

/**
 * Class DatabaseQueryTraitOrder
 * @package Cyan\Library
 * @since 1.0.0
 */
trait DatabaseQueryTraitOrder
{
    /**
     * Add order to a query
     *
     * @param $columns
     *
     * @return $this
     *
     * @since 1.0.0
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