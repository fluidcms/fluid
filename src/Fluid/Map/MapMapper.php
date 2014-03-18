<?php
namespace Fluid\Map;

use Fluid\MappingInterface;
use Fluid\StorageInterface;
use Fluid\Page\PageRepository;
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
     * @param MapEntity $map
     */
    public function map(MapEntity $map)
    {
        $this->mapObject($map, $this->getStorage()->load(self::DATA_FILENAME));
    }

    /**
     * @param MapEntity $map
     * @param array $data
     * @return MapEntity
     */
    protected function mapObject(MapEntity $map, array $data)
    {
        $this->getXmlMappingLoader()->load(self::MAPPING_FILENAME);
        if (isset($data['pages'])) {
            $map->setPages(new PageRepository($data['pages']));
        }
        return $map;
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