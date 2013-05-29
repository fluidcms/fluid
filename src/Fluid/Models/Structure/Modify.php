<?php

namespace Fluid\Models\Structure;

use Fluid\Models\Structure, Exception;

/**
 * Modify the structure.
 *
 * @package fluid
 */
class Modify
{
    /**
     * Add a page to the structure.
     *
     * @param   Structure   $structure
     * @param   string      $id
     * @param   int         $index
     * @param   string      $page
     * @param   string      $url
     * @param   string      $layout
     * @param   array       $languages
     * @param   array       $pages
     * @throws  Exception
     * @return  Structure
     */
    public static function addPage(Structure $structure, $id, $index, $page, $url, $layout, $languages, $pages = null)
    {
        $paths = explode("/", preg_replace("/\\/?{$page}$/", '', $id));

        $page = array(
            'page' => $page,
            'url' => $url,
            'layout' => $layout,
            'languages' => $languages
        );

        if (null !== $pages) {
            $page['pages'] = $pages;
        }

        $structure->pages = self::insertPageIntoPages(
            $structure->pages,
            $paths,
            $index,
            $page
        );

        return $structure;
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
                throw new Exception("Current structure does not match new structure");
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
     * Delete a page from the structure.
     *
     * @param   Structure   $structure
     * @param   string      $id
     * @return  Structure
     */
    public static function deletePage(Structure $structure, $id)
    {
        $structure->pages = self::removePageFromPages($structure->pages, explode("/", $id));
        return $structure;
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
     * Delete a page from the structure.
     *
     * @param   Structure   $structure
     * @param   string      $id
     * @param   string      $to
     * @param   int         $index
     * @return  Structure
     */
    public static function sortPage(Structure $structure, $id, $to, $index)
    {
        $page = self::getPage($structure->pages, explode("/", $id));
        self::deletePage($structure, $id);
        $to = "$to/{$page['page']}";
        $pages = isset($page['pages']) ? $page['pages'] : null;
        self::addPage($structure, $to, $index, $page['page'], $page['url'], $page['layout'], $page['languages'], $pages);

        return $structure;
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
}