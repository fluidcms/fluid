<?php
namespace Fluid\File;

use Countable;
use Fluid\RegistryInterface;
use JsonSerializable;
use Fluid\Registry;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;

class FileCollection implements Countable, IteratorAggregate, ArrayAccess, JsonSerializable
{
    /**
     * @var FileEntity[]
     */
    protected $files;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var FileMapper
     */
    private $mapper;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @param RegistryInterface $registry
     * @param FileMapper|null $mapper
     */
    public function __construct(RegistryInterface $registry, FileMapper $mapper = null)
    {
        $this->setStorage($registry->getStorage());
        $this->setXmlMappingLoader($registry->getXmlMappingLoader());
        if (null !== $mapper) {
            $this->setMapper($mapper);
        } else {
            $this->setMapper(new FileMapper($registry));
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        if (null === $this->files)  {
            $this->getMapper()->mapCollection($this);
        }

        $retval = [];

        foreach ($this->files as $file) {
            $retval[] = $file->jsonSerialize();
        }

        return $retval;
    }

    /**
     * @param string $id
     * @return FileEntity|null
     */
    public function find($id)
    {
        if (null === $this->files)  {
            $this->getMapper()->mapCollection($this);
        }

        foreach ($this->files as $file) {
            if ($file->getId() === $id) {
                return $file;
            }
        }
        return null;
    }

    /**
     * @param FileEntity $file
     * @return $this
     */
    public function add(FileEntity $file)
    {
        if (null === $this->files)  {
            $this->files = [];
        }
        $this->files[] = $file;
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
     * @return int
     */
    public function count()
    {
        return count($this->files);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->files);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->files[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->files[$offset];
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->files[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->files[$offset]);
    }
}