<?php
namespace Fluid\Component;

use Countable;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;
use Fluid\Exception\MissingMappingAttributeException;
use Fluid\Exception\InvalidDataException;

class ComponentCollection implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var ComponentEntity[]
     */
    protected $components;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var ComponentMapper
     */
    private $mapper;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @param StorageInterface $storage
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @param ComponentMapper|null $componentMapper
     */
    public function __construct(StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader, ComponentMapper $componentMapper = null)
    {
        $this->setStorage($storage);
        $this->setXmlMappingLoader($xmlMappingLoader);
        if (null !== $componentMapper) {
            $this->setMapper($componentMapper);
        } else {
            $this->setMapper(new ComponentMapper($storage, $xmlMappingLoader));
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if (null === $this->components)  {
            $this->getMapper()->mapCollection($this);
        }

        $retval = [];

        foreach ($this->components as $component) {
            $retval[] = $component->toArray();
        }

        return $retval;
    }

    /**
     * @param ComponentEntity $component
     * @return $this
     */
    public function add(ComponentEntity $component)
    {
        if (null === $this->components)  {
            $this->components = [];
        }
        $this->components[] = $component;
        return $this;
    }

    /**
     * @param StorageInterface $storage
     * @return $this
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @return $this
     */
    public function setXmlMappingLoader(XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->xmlMappingLoader = $xmlMappingLoader;
        return $this;
    }

    /**
     * @return XmlMappingLoaderInterface
     */
    public function getXmlMappingLoader()
    {
        return $this->xmlMappingLoader;
    }

    /**
     * @param ComponentMapper $mapper
     * @return $this
     */
    public function setMapper(ComponentMapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return ComponentMapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->components);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->components);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->components[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->components[$offset];
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->components[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->components[$offset]);
    }
}