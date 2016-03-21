<?php
namespace Cyan\Framework;

/**
 * Class QueryTraitLimit
 * @package Cyan\Framework
 * @since 1.0.0
 */
trait DatabaseQueryTraitLimit
{
    /**
     * Set Limit to Query
     *
     * @param $limit
     *
     * @return $this
     *
     * @since 1.0.0
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
     *
     * @since 1.0.0
     */
    public function offset($offset)
    {
        $this->offset = intval($offset);

        return $this;
    }
}