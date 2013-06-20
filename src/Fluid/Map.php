<?php

namespace Fluid;

use Fluid\Fluid,
    Exception;

/**
 * Site structure model
 *
 * @package fluid
 */
class Map extends Storage\FileSystem
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
     * Find a page in a structure.
     *
     * @param   string|array    $paths
     * @param   array           $pages
     * @return  array
     */
    public function findPage($paths, $pages = null)
    {
        if (null === $pages) {
            $pages = $this->pages;
        }
        if (!is_array($paths)) {
            $paths = explode('/', $paths);
        }

        $needle = reset($paths);
        $paths = array_slice($paths, 1);

        foreach($pages as $page) {
            if ($page['page'] == $needle) {
                if (isset($page['pages']) && count($paths)) {
                    if ($match = $this->findPage($paths, $page['pages'])) {
                        return $match;
                    }
                } else if (!count($paths)) {
                    return $page;
                }
            }
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
     * @return  bool
     */
    public function sortPage($id, $to, $index)
    {
        Page::move($id, $to);
        $structure = Structure\Modify::sortPage(new self, $id, $to, $index);
        $structure->store();

        return true;
    }

    /**
     * Save structure.
     *
     * @return  void
     */
    public function store()
    {
        self::save(json_encode($this->pages, JSON_PRETTY_PRINT), self::$dataFile);
    }
}