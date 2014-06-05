<?php
namespace Fluid\Component;

use Countable;
use Fluid\RegistryInterface;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Fluid\Variable\VariableCollection;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;

class ComponentEntity implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ComponentConfig
     */
    private $config;

    /**
     * @var VariableCollection
     */
    private $variables;

    /**
     * @var string
     */
    private $xmlMappingFile;

    /**
     * @var StorageInterface
     * @deprecated
     */
    private $storage;

    /**
     * @var XmlMappingLoaderInterface
     * @deprecated
     */
    private $xmlMappingLoader;

    /**
     * @var ComponentMapper
     * @deprecated
     */
    private $mapper;

    /**
     * @var ComponentCollection
     */
    private $collection;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry
     * @param ComponentCollection|null $collection
     */
    public function __construct(RegistryInterface $registry, ComponentCollection $collection = null)
    {
        $this->registry = $registry;
        $this->setStorage($this->registry->getStorage());
        $this->setXmlMappingLoader($this->registry->getXmlMappingLoader());
        if (null !== $collection) {
            $this->setCollection($collection);
        }
        $this->setConfig(new ComponentConfig($this));
        $this->setVariables(new VariableCollection($this->registry));
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'config' => $this->getConfig()->toArray(),
            'variables' => $this->getVariables()->toArray()
        ];
    }

    /**
     * @param ComponentCollection $collection
     * @return $this
     */
    public function setCollection(ComponentCollection $collection)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * @return ComponentCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param ComponentMapper $mapper
     * @return $this
     * @deprecated
     */
    public function setMapper(ComponentMapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return ComponentMapper
     * @deprecated
     */
    public function getMapper()
    {
        return $this->registry->getComponentMapper();
    }

    /**
     * @param ComponentConfig $config
     * @return $this
     */
    public function setConfig(ComponentConfig $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return ComponentConfig
     */
    public function getConfig()
    {
        return $this->config;
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
     * @param StorageInterface $storage
     * @return $this
     * @deprecated
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return StorageInterface
     * @deprecated
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param VariableCollection $variables
     * @return $this
     */
    public function setVariables(VariableCollection $variables)
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * @return VariableCollection
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param string $xmlMappingFile
     * @return $this
     */
    public function setXmlMappingFile($xmlMappingFile)
    {
        $this->xmlMappingFile = $xmlMappingFile;
        return $this;
    }

    /**
     * @return string
     */
    public function getXmlMappingFile()
    {
        return $this->xmlMappingFile;
    }

    /**
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @return $this
     * @deprecated
     */
    public function setXmlMappingLoader(XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->xmlMappingLoader = $xmlMappingLoader;
        return $this;
    }

    /**
     * @return XmlMappingLoaderInterface
     * @deprecated
     */
    public function getXmlMappingLoader()
    {
        return $this->xmlMappingLoader;
    }

    /**
     * @return int
     */
    public function count()
    {
        //return count($this->items);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        //return new ArrayIterator($this->items);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        //return isset($this->items[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        //return $this->getVariables()[$offset];
        //return $this->items[$offset];
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        //$this->items[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        //unset($this->items[$offset]);
    }
}