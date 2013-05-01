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
     * @throws  Exception
     * @return  Structure
     */
    public static function addPage(Structure $structure, $id, $index, $page, $url, $layout, $languages)
    {
        $paths = explode("/", preg_replace("/\\/?{$page}$/", '', $id));

        $structure->pages = self::insertPageIntoStructure(
            $structure->pages,
            $paths,
            $index,
            array(
                'page' => $page,
                'url' => $url,
                'layout' => $layout,
                'languages' => $languages
            )
        );

        return $structure;
    }

    /**
     * Add a page to the structure.
     *
     * @param   array   $pages
     * @param   string  $paths
     * @param   int     $index
     * @param   array   $newPage
     * @throws  Exception
     * @return  array
     */
    private static function insertPageIntoStructure($pages, $paths, $index, $newPage)
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
                    $pages[$key]['pages'] = self::insertPageIntoStructure($item["pages"], $paths, $index, $newPage);
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
}