<?php
namespace Fluid\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use ArrayAccess;
use Fluid\Map\MapEntity;
use Fluid\Page\PageEntity;
use Fluid\RegistryInterface;
use Fluid\Request;

class DataCollection implements Countable, IteratorAggregate, ArrayAccess
{
    const CREATE_EVENT = 'create';

    /**
     * @var MapEntity
     */
    private $map;

    /**
     * @var PageEntity
     */
    private $page;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param RegistryInterface $registry
     * @param Request $request
     * @param MapEntity $map
     * @param PageEntity $page
     */
    public function __construct(RegistryInterface $registry, Request $request, MapEntity $map, PageEntity $page)
    {
        $this->map = $map;
        $this->page = $page;

        $this->data = [
            'page' => $page,
            'map' => $map,
            'name' => $page->getName(),
            'language' => substr($page->getLanguage()->getLanguage(), 0, 2),
            'locale' => str_replace('_', '-', $page->getLanguage()->getLanguage()),
            'pages' => $page->getPages(),
            'parent' => $page->getParent(),
            'parents' => $page->getParents(),
            'url' => $page->getConfig()->getUrl(),
            'template' => $page->getConfig()->getTemplate(),
            'languages' => $page->getConfig()->getLanguages(),
            'allow_childs' => $page->getConfig()->getAllowChilds(),
            'child_templates' => $page->getConfig()->getChildTemplates(),
            'path' => explode('/', trim($request->getUri(), '/')),
            'global' => $map->findPage('global')
        ];

        $registry->getEventDispatcher()->trigger($this, self::CREATE_EVENT);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        return $this->offsetGet($name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}