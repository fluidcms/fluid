<?php

namespace Fluid\Map;

use Fluid\Fluid,
    Fluid\Page\Page,
    Fluid\Storage\FileSystem,
    Exception;

/**
 * Site structure model
 *
 * @package fluid
 */
class Map extends FileSystem
{
    protected static $dataFile = 'map.json';
    protected static $cacheKey = "map";

    private $pages;

    /**
     * Init
     */
    public function __construct()
    {
    }

    /**
     * Get pages
     *
     * @return  array
     */
    public function getPages()
    {
        if (null === $this->pages) {
            $this->pages = self::load();
        }
        return $this->pages;
    }

    /**
     * Set pages
     *
     * @param   array   $value
     * @return  void
     */
    public function setPages($value)
    {
        $this->pages = $value;
    }

    /**
     * Create a new page.
     *
     * @param   array   $attrs
     * @throws  Exception
     * @return  array
     */
    public function createPage($attrs)
    {
        Page::create($attrs['path'], $attrs['languages'], array());
        $structure = Structure\Modify::addPage(new self, $attrs["path"], $attrs["index"], $attrs["page"], $attrs["url"], $attrs["layout"], $attrs["languages"]);
        $structure->store();

        return array(
            "id" => $attrs["path"],
            "page" => $attrs["page"],
            "url" => $attrs["url"],
            "layout" => $attrs["layout"],
            "languages" => $attrs["languages"]
        );
    }

    /**
     * Find a page in the map.
     *
     * @param   string  $id
     * @return  array
     */
    public function findPage($id)
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
     * @param   array   $attrs
     * @return  bool
     */
    public function editPage($attrs)
    {
        list($structure, $id) = Structure\Modify::editPage(new self, $attrs["id"], $attrs["page"], $attrs["url"], $attrs["layout"], $attrs["languages"]);
        $structure->store();

        Page::rename($attrs["id"], $id);

        return array(
            "id" => $id,
            "page" => $attrs["page"],
            "url" => $attrs["url"],
            "layout" => $attrs["layout"],
            "languages" => $attrs["languages"]
        );
    }

    /**
     * Delete a page.
     *
     * @param   string   $id
     * @return  bool
     */
    public function deletePage($id)
    {
        Page::delete($id);
        $structure = Structure\Modify::deletePage(new self, $id);
        $structure->store();

        return true;
    }

    /**
     * Sort a page.
     *
     * @param   string   $id
     * @param   string   $to
     * @param   string   $index
     * @throws  \InvalidArgumentException
     * @return  bool
     */
    public function sortPage($id, $to, $index)
    {
        if ($page = $this->findPage($id)) {
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

        throw new \InvalidArgumentException();
    }

    /**
     * Save map.
     *,
     * @return  void
     */
    public function store()
    {
        self::save(json_encode($this->getPages(), JSON_PRETTY_PRINT), self::$dataFile);
    }
}