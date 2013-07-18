<?php

namespace Fluid\Map;

use Fluid\Map\Map, Exception;

/**
 * Modify the map.
 *
 * @package fluid
 */
class Modify
{
    /**
     * Add a page to the map.
     *
     * @param   Map         $map
     * @return  Map
     */
    public static function resetIds(Map $map)
    {
        $resetIds = function($pages, $parent = null) use (&$resetIds) {
            $output = array();
            foreach($pages as $page) {
                $page['id'] = trim($parent . "/" . $page['page'], "/");
                if (isset($page['pages']) && is_array($page['pages']) && count($page['pages']))  {
                    $page['pages'] = $resetIds($page['pages'], $page['id']);
                }
                $output[] = $page;
            }
            return $output;
        };

        $map->setPages($resetIds($map->getPages()));

        return $map;
    }

    /**
     * Add a page to the map.
     *
     * @param   Map         $map
     * @param   int         $index
     * @param   string      $id
     * @param   string      $page
     * @param   array       $languages
     * @param   string      $layout
     * @param   string      $url
     * @param   array       $pages
     * @throws  Exception
     * @return  Map
     */
    public static function addPage(Map $map, $index, $id, $page, $languages, $layout, $url, $pages = null)
    {
        $paths = explode("/", preg_replace("/\\/?{$page}$/", '', $id));

        $page = array(
            'id' => trim($id, '/'),
            'page' => $page,
            'url' => $url,
            'layout' => $layout,
            'languages' => $languages
        );

        if (null !== $pages) {
            $page['pages'] = $pages;
        }

        $map->setpages(self::insertPageIntoPages(
            $map->getPages(),
            $paths,
            $index,
            $page
        ));

        return self::resetIds($map);
    }

    /**
     * Edit a page in the map.
     *
     * @param   Map     $map
     * @param   string  $id
     * @param   string  $page
     * @param   array   $languages
     * @param   string  $layout
     * @param   string  $url
     * @throws  Exception
     * @return  Map
     */
    public static function editPage(Map $map, $id, $page, $languages, $layout, $url)
    {
        $map->setPages(self::findAndEditPage($map->getPages(), explode("/", $id), array(
            'page' => $page,
            'url' => $url,
            'layout' => $layout,
            'languages' => $languages
        )));

        $map = self::resetIds($map);

        $newId = trim(dirname($id) . '/' . $page, '/. ');

        if (self::getPage($map->getPages(), explode('/', $newId))) {
            return $map;
        } else {
            throw new Exception('Did not find the page to edit');
        }
    }

    /**
     * Delete a page from the map.
     *
     * @param   Map   $map
     * @param   string      $id
     * @return  Map
     */
    public static function deletePage(Map $map, $id)
    {
        $map->setPages(self::removePageFromPages($map->getPages(), explode("/", $id)));
        return self::resetIds($map);
    }

    /**
     * Insert the page into the pages.
     *
     * @param   array   $pages
     * @param   array   $paths
     * @param   int     $index
     * @param   array   $newPage
     * @throws  Exception
     * @return  array
     */
    private static function insertPageIntoPages($pages, $paths, $index, $newPage)
    {
        if (count($paths) && !empty($paths[0])) {
            $needle = reset($paths);
            $matched = false;
            foreach ($pages as $key => $item) {
                if ($item['page'] == $needle) {
                    $matched = true;
                    $paths = array_slice($paths, 1);
                    if (!isset($item["pages"]) || !is_array($item["pages"])) {
                        $item["pages"] = array();
                    }
                    $pages[$key]['pages'] = self::insertPageIntoPages($item["pages"], $paths, $index, $newPage);
                    break;
                }
            }
            if (!$matched) {
                throw new Exception("Current map does not match new map");
            }
        } else {
            $pages = array_merge(
                array_slice($pages, 0, $index),
                array($newPage),
                array_slice($pages, $index)
            );
        }

        return $pages;
    }

    /**
     * Remove a page from the pages.
     *
     * @param   array   $pages
     * @param   array   $paths
     * @return  array
     */
    private static function removePageFromPages($pages, $paths)
    {
        $needle = reset($paths);
        $paths = array_slice($paths, 1);

        foreach($pages as $key=>$page) {
            if ($pages[$key]['page'] == $needle) {
                if (count($paths) && isset($pages[$key]['pages'])) {
                    $pages[$key]['pages'] = self::removePageFromPages($pages[$key]['pages'], $paths);
                } else {
                    unset($pages[$key]);
                }
            }
            if (isset($pages[$key]['pages']) && !count($pages[$key]['pages'])) {
                unset($pages[$key]['pages']);
            }
        }

        return array_values($pages);
    }

    /**
     * Delete a page from the map.
     *
     * @param   Map   $map
     * @param   string      $id
     * @param   string      $to
     * @param   int         $index
     * @return  Map
     */
    public static function sortPage(Map $map, $id, $to, $index)
    {
        $page = self::getPage($map->getPages(), explode("/", $id));
        self::deletePage($map, $id);
        $to = "$to/{$page['page']}";
        $pages = isset($page['pages']) ? $page['pages'] : null;

        self::addPage($map, $index, $to, $page['page'], $page['languages'], $page['layout'], $page['url'], $pages);

        return self::resetIds($map);
    }

    /**
     * Get a page.
     *
     * @param   array   $pages
     * @param   array   $paths
     * @return  array
     */
    private static function getPage($pages, $paths)
    {
        $needle = reset($paths);
        $paths = array_slice($paths, 1);

        foreach($pages as $page) {
            if ($page['page'] == $needle) {
                if (count($paths) && isset($page['pages'])) {
                    $match = self::getPage($page['pages'], $paths);
                    if ($match) {
                        return $match;
                    }
                } else {
                    return $page;
                }
            }
        }

        return false;
    }

    /**
     * Find the page and edit it.
     *
     * @param   array   $pages
     * @param   array   $paths
     * @param   array   $data
     * @return  array
     */
    private static function findAndEditPage($pages, $paths, $data)
    {
        $needle = reset($paths);
        $paths = array_slice($paths, 1);

        foreach($pages as $key=>$page) {
            if ($pages[$key]['page'] == $needle) {
                if (count($paths) && isset($pages[$key]['pages'])) {
                    $pages[$key]['pages'] = self::findAndEditPage($pages[$key]['pages'], $paths, $data);
                } else {
                    $pages[$key] = array_merge($pages[$key], $data);
                }
            }
            if (isset($pages[$key]['pages']) && !count($pages[$key]['pages'])) {
                unset($pages[$key]['pages']);
            }
        }

        return array_values($pages);
    }
}