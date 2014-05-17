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
     * @var MapMapper
     */
    private $mapper;

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
     * @param MapMapper $mapper
     * @param Event $event
     * @param LanguageEntity $language
     */
    public function __construct(RegistryInterface $registry, MapMapper $mapper, Event $event = null, LanguageEntity $language)
    {
        $this->setRegistry($registry);
        $this->setMapper($mapper);
        $this->setLanguage($language);
        $this->setPages(new PageCollection($this->getRegistry(), $mapper->getStorage(), $mapper->getXmlMappingLoader(), null, $this->getLanguage()));
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
        if ($page instanceof PageEntity) {
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
     * @param MapMapper $mapper
     * @return $this
     */
    public function setMapper(MapMapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return MapMapper
     */
    public function getMapper()
    {
        return $this->mapper;
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
        return $this->getPages()->offsetSet($offset, $value);
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        return $this->getPages()->offsetUnset($offset);
    }
}