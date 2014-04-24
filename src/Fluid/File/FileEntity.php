<?php
namespace Fluid\File;

use Countable;
use Fluid\Token;
use JsonSerializable;
use Fluid\Container;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;

class FileEntity implements Countable, IteratorAggregate, ArrayAccess, JsonSerializable
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Container
     */
    private $container;

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
     * @var FileValidator
     */
    private $validator;

    /**
     * @param Container $container
     * @param FileMapper|null $mapper
     * @param FileCollection|null $collection
     */
    public function __construct(Container $container, FileMapper $mapper = null, FileCollection $collection = null)
    {
        $this->setContainer($container);
        $this->setStorage($container->getStorage());
        $this->setXmlMappingLoader($container->getXmlMappingLoader());
        if (null !== $mapper) {
            $this->setMapper($mapper);
        }
        if (null !== $collection) {
            $this->setCollection($collection);
        }
        $this->setId(Token::generate(8));
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName()
        ];
    }

    /**
     * @return mixed
     */
    public function isValid()
    {
        return $this->getValidator()->validate();
    }

    /**
     * @param array $uploadedFile
     * @return bool
     */
    public function validate(array $uploadedFile = null)
    {
        return $this->getValidator()->validate($uploadedFile);
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
        if (null === $this->mapper) {
            $this->setMapper(new FileMapper($this->getContainer()));
        }
        return $this->mapper;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @return FileValidator
     */
    public function getValidator()
    {
        if (null === $this->validator) {
            $this->setValidator(new FileValidator($this));
        }
        return $this->validator;
    }

    /**
     * @param FileValidator $validator
     * @return $this
     */
    public function setValidator(FileValidator $validator)
    {
        $this->validator = $validator;
        return $this;
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