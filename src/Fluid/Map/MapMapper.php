<?php
namespace Fluid\Map;

use Fluid\Event;
use Fluid\MappingInterface;
use Fluid\RegistryInterface;

class MapMapper
{
    const DATA_FILENAME = 'map.json';
    const MAPPING_FILENAME = 'map.xml';

    /**
     * @var Event
     * @deprecated
     */
    private $event;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry
     * @param Event $event
     */
    public function __construct(RegistryInterface $registry, Event $event)
    {
        $this->registry = $registry;
        $this->setEvent($event);
    }

    /**
     * @param MapEntity|null $map
     * @return MapEntity
     */
    public function map(MapEntity $map = null)
    {
        if (null === $map) {
            $map = new MapEntity($this->registry, $this->getEvent());
        }
        $this->mapObject($map, $this->registry->getStorage()->loadBranchData(self::DATA_FILENAME));
        return $map;
    }

    /**
     * @param MapEntity $map
     * @param array $data
     * @return MapEntity
     */
    public function mapObject(MapEntity $map, array $data = null)
    {
        $mapping = $this->registry->getXmlMappingLoader()->load(self::MAPPING_FILENAME);
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
     * @param Event $event
     * @return $this
     * @deprecated
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     * @deprecated
     */
    public function getEvent()
    {
        return $this->event;
    }
}