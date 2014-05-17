<?php
namespace Fluid\File;

use Fluid\File\Renderer\RenderImage;
use Fluid\Token;
use JsonSerializable;
use Fluid\RegistryInterface;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;
use Fluid\File\Renderer\RendererInterface;

class FileEntity implements JsonSerializable
{
    const TYPE_IMAGE = 'image';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type = self::TYPE_IMAGE;

    /**
     * @var RegistryInterface
     */
    private $registry;

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
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @param RegistryInterface $registry
     * @param FileMapper|null $mapper
     * @param FileCollection|null $collection
     */
    public function __construct(RegistryInterface $registry, FileMapper $mapper = null, FileCollection $collection = null)
    {
        $this->setRegistry($registry);
        $this->setStorage($registry->getStorage());
        $this->setXmlMappingLoader($registry->getXmlMappingLoader());
        if (null !== $mapper) {
            $this->setMapper($mapper);
        }
        if (null !== $collection) {
            $this->setCollection($collection);
        }
        $this->setId(Token::generate(8));
    }

    /**
     * @return string
     */
    public function render()
    {
        if (null === $this->renderer) {
            switch ($this->getType()) {
                case self::TYPE_IMAGE:
                    $this->renderer = new RenderImage($this->getRegistry(), $this);
                    break;
            }
        }
        return $this->renderer->render();
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
     * @return bool
     */
    public function hasVersion()
    {
        return false;
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
            $this->setMapper(new FileMapper($this->getRegistry()));
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
     * @return RegistryInterface
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @param RegistryInterface $registry
     * @return $this
     */
    public function setRegistry(RegistryInterface $registry)
    {
        $this->registry = $registry;
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}