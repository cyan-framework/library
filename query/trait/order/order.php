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

        return $this;
    }
}