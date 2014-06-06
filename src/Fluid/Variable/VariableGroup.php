<?php
namespace Fluid\Variable;

use Countable;
use Fluid\Language\LanguageEntity;
use Fluid\RegistryInterface;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;

class VariableGroup implements Countable, IteratorAggregate, ArrayAccess
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
     * @param array $variables
     * @return $this
     */
    public function reset(array $variables = null)
    {
        $this->variables = null;
        if (is_array($variables)) {
            $this->variables = [];
            foreach ($variables as $data) {
                if (isset($data['name']) && isset($data['type'])) {
                    $variable = new VariableEntity($this->registry);
                    $variable->setName($data['name']);
                    $variable->setType($data['type']);
                    if (isset($data['value'])) {
                        $variable->setValue($data['value']);
                    }
                    $this->add($variable);
                }
            }
        }
        return $this;
    }

    /**
     * @param VariableEntity $variable
     */
    public function add(VariableEntity $variable)
    {
        $this->variables[$variable->getName()] = $variable;
    }

    /**
     * @param string $name
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
            return $this->variables[$offset]->renderValue();
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