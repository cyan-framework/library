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
        $this->limit = intal($limit);

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
        $this->offset = intal($offset);

        return $this;
    }
}