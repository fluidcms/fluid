<?php
namespace Fluid\Variable;

use Countable;
use Fluid\Language\LanguageEntity;
use Fluid\RegistryInterface;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;

class VariableArray implements Countable, IteratorAggregate, ArrayAccess, VariableInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array|VariableEntity[]
     */
    private $variables;

    /**
     * @var array|VariableArrayItem[]
     */
    private $items;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry
     * @param LanguageEntity $language
     */
    public function __construct(RegistryInterface $registry, LanguageEntity $language = null)
    {
        $this->registry = $registry;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $variables = [];
        foreach ($this->variables as $variable) {
            $variables[] = $variable->toArray();
        }
        return [
            'name' => $this->getName(),
            'variables' => $variables
        ];
    }

    /**
     * @return array|VariableEntity[]
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param VariableEntity $variable
     */
    public function addVariable(VariableEntity $variable)
    {
        $this->variables[$variable->getName()] = $variable;
    }

    /**
     * @param VariableArrayItem $item
     */
    public function addItem(VariableArrayItem $item)
    {
        $this->items[] = $item;
    }

    /**
     * @param string $name+
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
        return count($this->items);
    }

    /**
     * @return ArrayIterator|VariableEntity[]|VariableGroup[]
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
        if (isset($this->items[$offset])) {
            return $this->items[$offset];
        }
        return null;
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