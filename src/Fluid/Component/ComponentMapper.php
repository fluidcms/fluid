<?php
namespace Fluid\Component;

use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;
use Fluid\Variable\VariableEntity;
use Fluid\Variable\VariableGroup;

class ComponentMapper
{
    const MAPPING_DIRECTORY = 'components';

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @param StorageInterface $storage
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     */
    public function __construct(StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->setStorage($storage);
        $this->setXmlMappingLoader($xmlMappingLoader);
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
            $component->setXmlMappingFile($file);
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
        $mapping = $this->getXmlMappingLoader()->load(self::MAPPING_DIRECTORY . DIRECTORY_SEPARATOR . $component->getXmlMappingFile() . '.xml');
        $component->getConfig()->set($mapping->getConfig());
        $variables = $component->getVariables();

        foreach ($mapping->getContent() as $key => $value) {
            /*if (isset($value['name']) && $value['name'] === 'variable') {
                $attributes = isset($value['attributes']) ? $value['attributes'] : [];
                $variable = new VariableEntity;
                $variable->set($attributes);
                $variables->addVariable($variable);
            } elseif (isset($value['name']) && $value['name'] === 'group') {
                $attributes = isset($value['attributes']) ? $value['attributes'] : [];
                $variableGroup = new VariableGroup();
                $variableGroup->setName(isset($attributes['name']) ? $attributes['name'] : null);
                foreach ($value as $groupVariable) {
                    if (isset($groupVariable['name']) && $groupVariable['name'] === 'variable') {
                        $attributes = isset($groupVariable['attributes']) ? $groupVariable['attributes'] : [];
                        $variable = new VariableEntity;
                        $variable->set($attributes);
                        $variableGroup->add($variable);
                    }
                }
                $variables->addVariable($variableGroup);
            }*/
        }

        return $component;
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