<?php
namespace Fluid\File;

use Fluid\Container;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;

class FileMapper
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setStorage($container->getStorage());
        $this->setXmlMappingLoader($container->getXmlMappingLoader());
    }

    /**
     * @param FileCollection|FileEntity[] $collection
     * @return FileCollection
     */
    public function mapCollection(FileCollection $collection)
    {
    }

    /**
     * @param FileEntity $component
     * @return FileEntity
     */
    public function mapEntity(FileEntity $component)
    {
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
}