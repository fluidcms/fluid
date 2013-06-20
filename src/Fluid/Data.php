<?php

namespace Fluid;

/**
 * Data class
 *
 * @package fluid
 */
class Data
{
    private static $map;

    /**
     * Get data for a page
     *
     * @param   string  $page
     * @return  array
     */
    public static function get($page)
    {
        if (!isset(self::$map)) {
            self::$map = new Map();
        }

        //$site = new Models\Site();
        $page = new Page($page);

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
            'url' => Router::getRequest(),
            'language' => substr(Fluid::getLanguage(), 0, 2),
            'site' => $site->data,
            'structure' => self::$structure->getLocalized(),
            'path' => explode('/', $page->page),
            'parents' => $parents,
            'parent' => $parentTree,
            'page' => (array) $page->data
        );
    }

    /**
     * Set the site map
     *
     * @param   Map $map
     * @return  void
     */
    public static function setMap(Map $map)
    {
        self::$map = $map;
    }

    /**
     * Get the site map
     *
     * @return  Map
     */
    public static function getMap()
    {
        return self::$map;
    }

    /**
     * Get the requested url
     * TODO: this is private because we only use it here, if we need to make if public, we will have to move it elsewhere
     *
     * @return  string
     */
    public static function getRequest()
    {
        // Determines if the connection with the client is secure.
        if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
            $secure = true;
        } else {
            $secure = false;
        }

        if (isset($_SERVER['SERVER_NAME']) && isset($_SERVER['REQUEST_URI'])) {
            return ($secure ? 'https://' : 'http://') . "{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
        }

        return '';
    }
}