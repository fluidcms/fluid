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

        // If rendering page for CMS, we change the current branch to the CMS branch and send a signal to the CMS
        /*if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING']);
            if (isset($fluidtoken) && isset($fluidbranch) && isset($fluidsession) && Token::validate($fluidtoken)) {
                Fluid::setBranch($fluidbranch);

                MessageQueue::send(array(
                    'task' => 'RequestedData',
                    'data' => array(
                        'session' => $fluidsession,
                        'message' => array(
                            'target' => 'data_request',
                            'data' => array(
                                'language' => Fluid::getLanguage(),
                                'page' => $page
                            )
                        )
                    )
                ));
            }
        }*/

        $global = self::getData(self::$map);

        $page = self::getPage($global, $page);

        $page = self::makeParentTree($page);

        $request = self::getRequest();
        $data = array(
            'url' => $request['url'],
            'domain' => $request['domain'],
            'path' => $request['path'],
            'ssl' => $request['ssl'],
            'language' => substr(Fluid::getLanguage(), 0, 2),
            'global' => $global,
            'parents' => $page['parents'],
            'parent' => isset($page['parent']) ? $page['parent'] : array(),
            'page' => $page
        );

        foreach(Event::trigger('data:get', array($data)) as $retval) {
            $data = $retval;
        }

        return $data;
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
     * @return  array
     */
    private static function getRequest()
    {
        // Determines if the connection with the client is secure.
        if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
            $secure = true;
        } else {
            $secure = false;
        }

        $url = '';
        $domain = '';
        $path = '';
        if (isset($_SERVER['SERVER_NAME']) && isset($_SERVER['REQUEST_URI'])) {
            $url = ($secure ? 'https://' : 'http://') . "{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
            $domain = $_SERVER['SERVER_NAME'];
            $path = $_SERVER['REQUEST_URI'];
        }

        return array(
            'url' => $url,
            'domain' => $domain,
            'path' => $path,
            'ssl' => $secure
        );
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
        $data = self::processPagesKeys($data);
        return $data;
    }

    /**
     * Add keys to sub pages
     *
     * @param   array   $page
     * @return  array
     */
    private static function processPagesKeys($page)
    {
        if (isset($page['pages']) && is_array($page['pages']) && count($page['pages'])) {
            $newPages = array();
            foreach($page['pages'] as $key => $childPage) {
                $newPages[$childPage['page']] = self::processPagesKeys($childPage);
            }
            $page['pages'] = $newPages;
        }
        return $page;
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
            $newParents = array();
            foreach($page['parents'] as $parent) {
                $newParents[$parent['page']] = $parent;
            }
            $page['parents'] = $newParents;

            $page['parent'] = current($page['parents']);
            $page['parent']['parents'] = array_slice($page['parents'], 1);
            $page['parent'] = self::makeParentTree($page['parent']);
        }

        return $page;
    }
}