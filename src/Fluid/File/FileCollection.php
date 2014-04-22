<?php
namespace Fluid\File;

use Countable;
use Fluid\Container;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;

class FileCollection implements Countable, IteratorAggregate, ArrayAccess
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
     * @var FileMapping
     */
    private $mapper;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @param Container $container
     * @param FileMapping|null $mapper
     */
    public function __construct(Container $container, FileMapping $mapper = null)
    {
        $this->setStorage($container->getStorage());
        $this->setXmlMappingLoader($container->getXmlMappingLoader());
        if (null !== $mapper) {
            $this->setMapper($mapper);
        } else {
            $this->setMapper(new FileMapping($container));
        }
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
     * @param FileMapping $mapper
     * @return $this
     */
    public function setMapper(FileMapping $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return FileMapping
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