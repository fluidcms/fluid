<?php
namespace Fluid\Map;

class MapConfig
{
    /**
     * @var MapEntity
     */
    private $map;

    /**
     * @param MapEntity $map
     */
    public function __construct(MapEntity $map)
    {
        $this->setMap($map);
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function set(array $attributes = [])
    {
        return $this;
    }

    /**
     * @param MapEntity $map
     * @return $this
     */
    public function setMap(MapEntity $map)
    {
        $this->map = $map;
        return $this;
    }

    /**
     * @return MapEntity
     */
    public function getMap()
    {
        return $this->map;
    }
}