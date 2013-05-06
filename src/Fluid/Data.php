<?php

namespace Fluid;

/**
 * Data class
 *
 * @package fluid
 */
class Data
{
    private static $structure;
    private static $page;

    /**
     * Get data for a page
     *
     * @param   string  $page
     * @return  array
     */
    public static function get($page)
    {
        if (!isset(self::$structure)) {
            self::$structure = new Models\Structure();
        }

        self::$page = $page;

        $site = new Models\Site();
        $page = new Models\Page(self::$structure, $page);

        // Make parents tree
        $parentTree = null;
        $current = $page;
        $parents = array();

        while ($current->parent instanceof Models\Page) {
            $current = $current->parent;
            $parents[] = $current->data;
        }

        foreach ($parents as $parent) {
            if (!isset($parentTree)) {
                $parentTree = $parent;
                $last = & $parentTree;
            } else {
                $last['parent'] = $parent;
                $last = & $last['parent'];
            }
        }

        return array(
            'language' => substr(Fluid::getLanguage(), 0, 2),
            'site' => $site->data,
            'structure' => self::$structure->getLocalized(),
            'path' => explode('/', $page->page),
            'parents' => $parents,
            'parent' => $parentTree,
            'page' => array_merge((array) $page->data, self::$structure->findPage($page->page))
        );
    }

    /**
     * Set the site structure
     *
     * @param   Models\Structure    $structure
     * @return  void
     */
    public static function setStructure(Models\Structure $structure)
    {
        self::$structure = $structure;
    }

    /**
     * Get the site structure
     *
     * @return  Models\Structure
     */
    public static function getStructure()
    {
        return self::$structure;
    }

    /**
     * Get a list of the pages requested by the ::get() method
     *
     * @return  array
     */
    public static function getPage()
    {
        return self::$page;
    }
}