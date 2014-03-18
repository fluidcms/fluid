<?php
namespace Fluid\Map;

use Exception;
use InvalidArgumentException;
use Fluid\Page\PageRepository;
use Fluid\Page\PageEntity;

/**
 * Map Entity
 *
 * @package fluid
 */
class MapEntity
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
     * @var PageRepository
     */
    private $pages;

    /**
     * Init
     */
    public function __construct(MapMapper $mapper)
    {
        $this->setMapper($mapper);
        $this->setPages(new PageRepository(null, $mapper->getStorage(), $mapper->getXmlMappingLoader()));
        $this->setConfig(new MapConfig($this));
    }

    /**
     * @param $page
     * @return PageEntity
     */
    public function findPage($page)
    {
        return $this->getPages()->find($page);
    }

    /**
     * @param PageRepository $pages
     * @return $this
     */
    public function setPages(PageRepository $pages)
    {
        $this->pages = $pages;
        return $this;
    }

    /**
     * @return PageRepository
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


    /////////////////////

    /**
     * Get pages
     *
     * @return array
     */
    public function ___getPages()
    {
        if (null === $this->pages) {
            $this->pages = self::load();
        }
        return $this->pages;
    }

    /**
     * Set pages
     *
     * @param array $value
     * @return void
     */
    public function ___setPages(array $value)
    {
        $this->pages = $value;
    }

    /**
     * Create a new page.
     *
     * @param array $attrs
     * @throws Exception
     * @return array
     */
    public function ___createPage(array $attrs)
    {
        if (!is_int($attrs['index'])) {
            throw new InvalidArgumentException;
        }

        $page = Page::create($attrs['page'], $attrs['parent'], $attrs['languages'], $attrs['layout'], $attrs['url']);

        $map = Modify::addPage($this, $attrs['index'], $page['id'], $page['page'], $page['languages'], $page['layout'], $page['url']);
        $map->store();

        return true;
    }

    /**
     * Find a page in the map.
     *
     * @param string $id
     * @return array
     */
    public function __findPage($id)
    {
        $find = function($paths, $pages) use (&$find) {
            $needle = reset($paths);
            $paths = array_slice($paths, 1);

            foreach($pages as $page) {
                if ($page['page'] == $needle) {
                    if (isset($page['pages']) && count($paths)) {
                        if ($match = $find($paths, $page['pages'])) {
                            return $match;
                        }
                    } else if (!count($paths)) {
                        return $page;
                    }
                }
            }

            return false;
        };

        if ($match = $find(explode('/', $id), $this->getPages())) {
            return $match;
        }

        return false;
    }

    /**
     * Edit a page.
     *
     * @param array $attrs
     * @return bool
     */
    public function editPage(array $attrs)
    {
        $page = Page::config($attrs['id'], $attrs['page'], $attrs['languages'], $attrs['layout'], $attrs['url']);

        $map = Modify::editPage($this, $attrs["id"], $attrs["page"], $attrs["languages"], $attrs["layout"], $attrs["url"]);
        $map->store();

        return true;
    }

    /**
     * Delete a page.
     *
     * @param string $id
     * @throws InvalidArgumentException
     * @return bool
     */
    public function deletePage($id)
    {
        if ($page = $this->findPage($id)) {
            Page::get($page['id'])->delete();

            $map = Modify::deletePage($this, $id);
            $map->store();

            return true;
        }

        throw new \InvalidArgumentException();
    }

    /**
     * Sort a page.
     *
     * @param string $id
     * @param string $to
     * @param string $index
     * @throws InvalidArgumentException
     * @return bool
     */
    public function sortPage($id, $to, $index)
    {
        if ($page = $this->findPage($id)) {
            // TODO: move this part to the page model like in the delete method
            $movePages = function($pages, $to, $parent = null) use (&$movePages) {
                foreach($pages as $page) {
                    $to = trim($to . '/' . $parent, '/');
                    $parent = trim($parent . '/' . basename($page['id']), '/');

                    if (isset($page['pages']) && is_array($page['pages']) && count($page['pages'])) {
                        $movePages($page['pages'], $to, $parent);
                    }

                    Page::get($page['id'])->move($to);
                }
            };

            $movePages(array($page), $to);

            $map = Modify::sortPage($this, $id, $to, $index);
            $map->store();

            return true;
        }

        throw new InvalidArgumentException();
    }

    /**
     * Save map.
     */
    public function store()
    {
        self::save(json_encode($this->getPages(), JSON_PRETTY_PRINT), self::$dataFile);
    }
}