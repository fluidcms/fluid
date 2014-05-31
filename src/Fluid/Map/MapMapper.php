<?php
namespace Fluid\Map;

use Fluid\Event;
use Fluid\Language\LanguageEntity;
use Fluid\MappingInterface;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;
use Fluid\RegistryInterface;

class MapMapper
{
    const DATA_FILENAME = 'map.json';
    const MAPPING_FILENAME = 'map.xml';

    /**
     * @var StorageInterface
     * @deprecated Use registry instead
     */
    private $storage;

    /**
     * @var XmlMappingLoaderInterface
     * @deprecated Use registry instead
     */
    private $xmlMappingLoader;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var LanguageEntity
     */
    private $language;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry
     * @param StorageInterface $storage
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @param Event $event
     * @param LanguageEntity $language
     */
    public function __construct(RegistryInterface $registry, StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader, Event $event, LanguageEntity $language)
    {
        $this->setRegistry($registry);
        $this->setStorage($storage);
        $this->setXmlMappingLoader($xmlMappingLoader);
        $this->setEvent($event);
        $this->setLanguage($language);
    }

    /**
     * @param MapEntity|null $map
     * @return MapEntity
     */
    public function map(MapEntity $map = null)
    {
        if (null === $map) {
            $map = new MapEntity($this->getRegistry(), $this, $this->getEvent(), $this->getLanguage());
        }
        $this->mapObject($map, $this->getStorage()->loadBranchData(self::DATA_FILENAME));
        return $map;
    }

    /**
     * @param MapEntity $map
     * @param array $data
     * @return MapEntity
     */
    public function mapObject(MapEntity $map, array $data = null)
    {
        $mapping = $this->getXmlMappingLoader()->load(self::MAPPING_FILENAME);
        $map->getConfig()->set($mapping->getConfig());
        $this->mapXmlObject($map, $mapping);

        $map->getPages()->addGlobalPage();
        if (is_array($data)) {
            $count = 0;
            foreach ($data as $page) {
                $page = $map->getPages()->addPage($page);
                $map->getPages()->order($page, $count);
                $count++;
            }
        }

        return $map;
    }

    /**
     * @param MapEntity $map
     * @param MappingInterface $mapping
     */
    public function mapXmlObject(MapEntity $map, MappingInterface $mapping)
    {
        $content = $mapping->getContent();
        if (is_array($content)) {
            foreach ($content as $item) {
                if (isset($item['name']) && $item['name'] === 'page') {
                    $map->getPages()->addPageMapping($item);
                }
            }
        }
    }

    /**
     * @param StorageInterface $storage
     * @return $this
     * @deprecated Use registry instead
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return StorageInterface
     * @deprecated Use registry instead
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @return $this
     * @deprecated Use registry instead
     */
    public function setXmlMappingLoader(XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->xmlMappingLoader = $xmlMappingLoader;
        return $this;
    }

    /**
     * @return XmlMappingLoaderInterface
     * @deprecated Use registry instead
     */
    public function getXmlMappingLoader()
    {
        return $this->xmlMappingLoader;
    }

    /**
     * @param Event $event
     * @return $this
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param LanguageEntity $language
     * @return $this
     */
    public function setLanguage(LanguageEntity $language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return LanguageEntity
     */
    public function getLanguage()
    {
        return $this->language;
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
}