<?php
namespace Fluid\Data;

use Fluid\Map\MapEntity;
use Fluid\Page\PageEntity;
use Fluid\RegistryInterface;
use Fluid\Request;

class DataMapper
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var MapEntity
     */
    private $map;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param PageEntity $page
     * @return DataCollection
     */
    public function mapPage(PageEntity $page)
    {
        return new DataCollection($this->registry, $this->getRequest(), $this->getMap(), $page);
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

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}