<?php

/**
 * This file is a part of JsonPolicy project.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

/**
 * Car Dealership
 */
class Dealership implements Iterator, ArrayAccess
{

    /**
     * Dealership stock
     *
     * @var array
     *
     * @access protected
     */
    protected $in_stock = [];

    /**
     * Constructor
     *
     * @param array $cars
     */
    public function __construct(array $cars = [])
    {
        $this->in_stock = $cars;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        reset($this->in_stock);
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return current($this->in_stock);
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return key($this->in_stock);
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        return next($this->in_stock);
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        $key = key($this->in_stock);

        return ($key !== null && $key !== false);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->in_stock[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->in_stock[$offset];
    }

    /**
     * @inheritDoc
     */
    public  function offsetSet($offset, $value)
    {
        $this->in_stock[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->in_stock[$offset]);
    }

}