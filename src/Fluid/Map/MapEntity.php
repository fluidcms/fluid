<?php
namespace Fluid\Map;

use Countable;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Exception;
use Fluid\Event;
use Fluid\Language\LanguageEntity;
use InvalidArgumentException;
use Fluid\Page\PageCollection;
use Fluid\Page\PageEntity;
use Fluid\RegistryInterface;

/**
 * Map Entity
 *
 * @package fluid
 */
class MapEntity implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var MapConfig
     */
    private $config;

    /**
     * @var PageCollection|PageEntity[]
     */
    private $pages;

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
    public function __construct(RegistryInterface $registry, Event $event = null)
    {
        $this->setRegistry($registry);
        $this->setPages(new PageCollection($this->getRegistry()));
        $this->setConfig(new MapConfig($this));
        if (null !== $event) {
            $this->setEvent($event);
        }
    }

    /**
     * @param PageCollection|PageEntity[] $pages
     * @return array
     */
    public function toArray($pages = null)
    {
        if (null === $pages) {
            $pages = $this->getPages();
        }
        $retval = [];
        foreach ($pages as $page) {
            $retval[] = [
                'id' => $page->getId(),
                'name' => $page->getName(),
                'pages' => $this->toArray($page->getPages()),
                'languages' => $page->getConfig()->getLanguages(),
                'allow_childs' => $page->getConfig()->getAllowChilds(),
                'child_templates' => $page->getConfig()->getChildTemplates(),
                'template' => $page->getConfig()->getTemplate(),
                'url' => $page->getConfig()->getUrl()
            ];
        }
        return $retval;
    }

    /**
     * @param $page
     * @return PageEntity
     */
    public function findPage($page)
    {
        $page = $this->getPages()->find($page);
        if ($page instanceof PageEntity && !$page->isGlobalPage()) {
            $this->getEvent()->triggerWebsocketEvent('website:page:change', ['page' => $page->getName()]);
        }
        return $page;
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

    /**
     * @param PageCollection $pages
     * @return $this
     */
    public function setPages(PageCollection $pages)
    {
        $this->pages = $pages;
        return $this;
    }

    /**
     * @return PageCollection
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param MapConfig $config
     * @return $this
     */
    public function setConfig(MapConfig $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return MapConfig
     */
    public function getConfig()
    {
        return $this->config;
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

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->getPages()->__isset($name);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        return $this->getPages()->__call($name, $arguments);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getPages()->__get($name);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->getPages()->count();
    }

    /**
     * @return ArrayIterator|PageEntity[]
     */
    public function getIterator()
    {
        return $this->getPages()->getIterator();
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->getPages()->offsetExists($offset);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getPages()->offsetGet($offset);
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->getPages()->offsetSet($offset, $value);
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        $this->getPages()->offsetUnset($offset);
    }
}