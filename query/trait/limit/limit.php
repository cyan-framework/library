<?php
namespace Cyan\Library;

/**
 * Class QueryTraitLimit
 * @package Cyan\Library
 */
trait QueryTraitLimit
{
    /**
     * Set Limit to Query
     *
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = intval($limit);

        return $this;
    }

    /**
     * Set Offset to Query
     *
     * @param $offset
     *
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = intval($offset);

        return $this;
    }
}