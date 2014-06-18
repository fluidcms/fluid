<?php
namespace Fluid\Variable;

use Countable;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;

class VariableArrayItem implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var VariableEntity[]
     */
    private $variables;

    /**
     * @param VariableEntity|VariableImage $variable
     */
    public function add($variable)
    {
        if (!$variable instanceof VariableEntity && !$variable instanceof VariableImage) {
            throw new \InvalidArgumentException;
        }
        $this->variables[$variable->getName()] = $variable;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        return $this->offsetGet($name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->variables);
    }

    /**
     * @return ArrayIterator|VariableEntity[]|VariableGroup[]
     */
    public function getIterator()
    {
        return new ArrayIterator($this->variables);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->variables[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (isset($this->variables[$offset])) {
            return $this->variables[$offset];
        }
        return null;
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->variables[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->variables[$offset]);
    }
}