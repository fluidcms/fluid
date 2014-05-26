<?php
namespace Fluid;

use Fluid\Map\MapEntity;
use Fluid\Page\PageEntity;

interface DataInterface
{
    /**
     * @param PageEntity $page
     * @return array
     */
    public function getPageData(PageEntity $page);

    /**
     * @param MapEntity $map
     * @return $this
     */
    public function setMap(MapEntity $map);

    /**
     * @return MapEntity
     */
    public function getMap();

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request);

    /**
     * @return Request
     */
    public function getRequest();
}