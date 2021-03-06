<?php
namespace Fluid\Variable;

use Countable;
use Fluid\RegistryInterface;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Fluid\Page\PageEntity;

class VariableCollection implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var array|VariableEntity[]|VariableGroup[]
     */
    protected $variables;

    /**
     * @var PageEntity
     */
    private $page;

    /**
     * @var bool
     */
    private $isMapped = false;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry
     * @param PageEntity $page
     */
    public function __construct(RegistryInterface $registry, PageEntity $page = null)
    {
        $this->registry = $registry;
        if (null !== $page) {
            $this->setPage($page);
        }
    }

    public function mapCollection()
    {
        if (null !== $this->getPage()) {
            $mapper = $this->registry->getVariableMapper();
            $mapper->mapCollection($this);
            $this->setIsMapped(true);
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $retval = [];
        if (null !== $this->variables) {
            foreach ($this->getIterator() as $variable) {
                $retval[] = $variable->toArray();
            }
        }
        return $retval;
    }

    /**
     * @param string $name
     * @return null|VariableEntity|VariableGroup
     */
    public function find($name)
    {
        if (isset($this->variables[$name])) {
            return $this->variables[$name];
        }
        return null;
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
                    $this->addVariable($variable);
                } elseif (isset($data['name']) && isset($data['variables'])) {
                    $variable = new VariableGroup($this->registry);
                    $variable->setName($data['name']);
                    $variable->reset($data['variables']);
                    $this->addVariable($variable);
                }
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function persist()
    {
        $this->registry->getVariableMapper()->persist($this);
        return $this;
    }

    /**
     * @param VariableEntity|VariableGroup|VariableImage $variable
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addVariable($variable)
    {
        if (!$variable instanceof VariableGroup && !$variable instanceof VariableEntity && !$variable instanceof VariableImage) {
            throw new \InvalidArgumentException;
        }
        $this->variables[$variable->getName()] = $variable;
        return $this;
    }

    /**
     * @param array $variables
     * @return $this
     */
    public function addVariables(array $variables)
    {
        return $this;
    }

    /**
     * @param PageEntity $page
     * @return $this
     */
    public function setPage(PageEntity $page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return PageEntity
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return bool
     */
    public function isMapped()
    {
        return $this->isMapped;
    }

    /**
     * @param bool $isMapped
     * @return $this
     */
    public function setIsMapped($isMapped)
    {
        $this->isMapped = $isMapped;
        return $this;
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
        if (!$this->isMapped()) {
            $this->mapCollection();
        }
        return count($this->variables);
    }

    /**
     * @return ArrayIterator|VariableEntity[]|VariableGroup[]
     */
    public function getIterator()
    {
        if (!$this->isMapped()) {
            $this->mapCollection();
        }
        return new ArrayIterator($this->variables);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (!$this->isMapped()) {
            $this->mapCollection();
        }
        return isset($this->variables[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (!$this->isMapped()) {
            $this->mapCollection();
        }
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
        if (!$this->isMapped()) {
            $this->mapCollection();
        }
        $this->variables[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        if (!$this->isMapped()) {
            $this->mapCollection();
        }
        unset($this->variables[$offset]);
    }
}