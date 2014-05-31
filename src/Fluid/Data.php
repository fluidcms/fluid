<?php
namespace Fluid;

use Fluid\Page\PageEntity;
use Fluid\Map\MapEntity;

class Data implements DataInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var MapEntity
     */
    private $map;

    /**
     * @param PageEntity $page
     * @return array
     */
    public function getPageData(PageEntity $page)
    {
        return [
            'page' => $page,
            'map' => $this->getMap(),
            'name' => $page->getName(),
            'language' => substr($page->getLanguage()->getLanguage(), 0, 2),
            'locale' => str_replace('_', '-', $page->getLanguage()->getLanguage()),
            'pages' => $page->getPages(),
            'parent' => $page->getParent(),
            'url' => $page->getConfig()->getUrl(),
            'template' => $page->getConfig()->getTemplate(),
            'languages' => $page->getConfig()->getLanguages(),
            'allow_childs' => $page->getConfig()->getAllowChilds(),
            'child_templates' => $page->getConfig()->getChildTemplates(),
            'path' => explode('/', trim($this->getRequest()->getUri(), '/')),
            'global' => $this->getMap()->findPage('global')
        ];
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