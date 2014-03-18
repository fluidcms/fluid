<?php
namespace Fluid\Map;

use Fluid\MappingInterface;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;

class MapMapper
{
    const DATA_FILENAME = 'map.json';
    const MAPPING_FILENAME = 'map.xml';

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
     * @param MapEntity|null $map
     * @return MapEntity
     */
    public function map(MapEntity $map = null)
    {
        if (null === $map) {
            $map = new MapEntity($this);
        }
        $this->mapObject($map, $this->getStorage()->load(self::DATA_FILENAME));
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

        if (is_array($data['pages'])) {
            foreach ($data['pages'] as $page) {
                $map->getPages()->addPage($page);
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