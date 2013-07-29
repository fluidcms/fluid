<?php

namespace Fluid;

use Fluid\Token\Token,
    Fluid\Page\Page,
    Fluid\Map\Map;

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
    public static function get($page = null)
    {
        if (!isset(self::$map)) {
            self::$map = new Map();
        }

        // If a valid token is provided, function will output the page Fluid data with the page content
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING']);
            if (isset($fluidtoken) && Token::validateToken($fluidtoken)) {
                Fluid::setBranch($fluidbranch);
                
                //var_dump($fluidbranch, $fluidtoken, $fluidsession);
                //die();
                //return self::renderWithFields($file, $data);
            }
        }

        $global = self::getData(self::$map);

        $page = self::getPage($global, $page);

        $page = self::makeParentTree($page);

        return array(
            'url' => self::getRequest(),
            'language' => substr(Fluid::getLanguage(), 0, 2),
            'global' => $global,
            'parents' => $page['parents'],
            'parent' => isset($page['parent']) ? $page['parent'] : array(),
            'page' => $page
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
     * NOTE: this is private because we only use it here, if we need to make if public, we will have to move it elsewhere
     *
     * @return  string
     */
    private static function getRequest()
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

    /**
     * Get all data
     *
     * @param   Map     $map
     * @return  array
     */
    private static function getData(Map $map)
    {
        $data = Page::get()->getData();
        $data['pages'] = self::getPagesData($map->getPages());
        return $data;
    }

    /**
     * Get pages data
     *
     * @param   array   $pages
     * @return  array
     */
    private static function getPagesData($pages)
    {
        $data = array();
        foreach($pages as $page) {
            // Get the page data
            $pageData = Page::get($page)->getData();
            $pageData = array_merge($page, $pageData);

            // Get the page pages
            if (isset($page['pages']) && is_array($page['pages']) && count($page['pages'])) {
                $pageData['pages'] = self::getPagesData($page['pages']);
            } else {
                unset($pageData['pages']);
            }

            $data[] = $pageData;
        }

        return $data;
    }

    /**
     * Get a page
     *
     * @param   array   $data
     * @param   string  $page
     * @return  array
     */
    private static function getPage($data, $page = null)
    {
        if (!empty($page)) {
            $page = self::findPage($page, $data['pages']);
            if (is_array($page['parents'])) {
                $page['parents'] = array_reverse($page['parents']);
            } else {
                $page['parents'] = array();
            }
            return $page;
        }
        return array();
    }

    /**
     * Find a page
     *
     * @param   string  $pageId
     * @param   array   $pages
     * @param   array   $parents
     * @return  array
     */
    private static function findPage($pageId, $pages, $parents = array())
    {
        foreach($pages as $page) {
            if (strtolower($page['id']) === strtolower($pageId)) {
                return array_merge($page, array('parents' => $parents));
            }
            else if (isset($page['pages']) && is_array($page['pages']) && count($page['pages'])) {
                $match = self::findPage($pageId, $page['pages'], array_merge($parents, array($page)));
                if ($match) {
                    return $match;
                }
            }
        }

        return null;
    }

    /**
     * Make the tree of parents for the requested page
     *
     * @param   array   $page
     * @return  array
     */
    private static function makeParentTree($page)
    {
        if (count($page['parents'])) {
            $page['parent'] = current($page['parents']);
            $page['parent']['parents'] = array_slice($page['parents'], 1);
            $page['parent'] = self::makeParentTree($page['parent']);
        }

        return $page;
    }
}