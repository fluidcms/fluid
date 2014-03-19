<?php
namespace Fluid;

use Countable;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;

abstract class Collection implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
}