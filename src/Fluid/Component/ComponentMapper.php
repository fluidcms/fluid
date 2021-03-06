<?php
namespace Fluid\Component;

use Fluid\RegistryInterface;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;
use Fluid\Variable\VariableEntity;
use Fluid\Variable\VariableGroup;

class ComponentMapper
{
    const MAPPING_DIRECTORY = 'components';
    const FILE_EXTENSION = '.xml';

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
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
        $this->setStorage($this->registry->getStorage());
        $this->setXmlMappingLoader($this->registry->getXmlMappingLoader());
    }

    /**
     * @param ComponentCollection|ComponentEntity[] $collection
     * @return ComponentCollection
     */
    public function mapCollection(ComponentCollection $collection)
    {
        $files = $this->getXmlMappingLoader()->filelist(self::MAPPING_DIRECTORY);
        foreach ($files as $file) {
            $component = new ComponentEntity($this->getStorage(), $this->getXmlMappingLoader(), $this, $collection);
            $component->setXmlMappingFile(self::MAPPING_DIRECTORY . DIRECTORY_SEPARATOR . $file);
            $component = $this->mapEntity($component);
            if (null !== $component) {
                $collection->add($component);
            }
        }
        return $collection;
    }

    /**
     * @param ComponentEntity $component
     * @return ComponentEntity
     */
    public function mapEntity(ComponentEntity $component)
    {
        if (null === $component->getXmlMappingFile()) {
            $component->setXmlMappingFile(self::MAPPING_DIRECTORY . DIRECTORY_SEPARATOR . $component->getName() . self::FILE_EXTENSION);
        }
        $mapping = $this->registry->getXmlMappingLoader()->load($component->getXmlMappingFile());

        if (!count($mapping->getContent()) && !count($mapping->getConfig())) {
            return null;
        }

        $component->getConfig()->set($mapping->getConfig());

        $this->registry->getVariableMapper()->mapXmlObject($mapping,  $component->getVariables());
        return $component;
    }

    /**
     * @param array $attributes
     * @return ComponentEntity
     */
    public function mapObject(array $attributes)
    {
        $component = new ComponentEntity($this->registry);
        if (isset($attributes['component'])) {
            $component->setName($attributes['component']);
        }
        $this->mapEntity($component);

        if (isset($attributes['variables'])) {
            $this->registry->getVariableMapper()->mapJsonCollection($component->getVariables(), $attributes['variables']);
        }
        return $component;
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
}