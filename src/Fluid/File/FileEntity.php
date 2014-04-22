<?php
namespace Fluid\File;

use Countable;
use Fluid\Container;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;

class FileEntity implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @var FileMapper
     */
    private $mapper;

    /**
     * @var FileCollection
     */
    private $collection;

    /**
     * @param Container $container
     * @param FileMapper|null $mapper
     * @param FileCollection|null $collection
     */
    public function __construct(Container $container, FileMapper $mapper = null, FileCollection $collection = null)
    {
        $this->setStorage($container->getStorage());
        $this->setXmlMappingLoader($container->getXmlMappingLoader());
        if (null !== $mapper) {
            $this->setMapper($mapper);
        }
        if (null !== $collection) {
            $this->setCollection($collection);
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
        ];
    }

    /**
     * @param FileCollection $collection
     * @return $this
     */
    public function setCollection(FileCollection $collection)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * @return FileCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param FileMapper $mapper
     * @return $this
     */
    public function setMapper(FileMapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return FileMapper
     */
    public function getMapper()
    {
        return $this->mapper;
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