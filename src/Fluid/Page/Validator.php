<?php

namespace Fluid\Page;

use Fluid\Map\Map;
use Fluid\Language\Language;
use Fluid\Layout\Layout;
use Exception;

/**
 * Validate page
 *
 * @package fluid
 */
class Validator
{
    /**
     * New page validator
     *
     * @param string $page
     * @param string $parent
     * @param array $languages
     * @param string $layout
     * @param string $url
     * @throws Exception
     * @return bool
     */
    public static function newPageValidator($page, $parent, array $languages, $layout, $url)
    {
        $map = new Map;
        if (!empty($parent) && !$map->findPage($parent)) {
            throw new Exception("Parent page does not exists");
        }

        self::pageValidator($page, $languages, $layout, $url);
    }

    /**
     * Page validator
     *
     * @param string $page
     * @param array $languages
     * @param string $layout
     * @param string $url
     * @throws Exception
     * @return bool
     */
    public static function pageValidator($page, array $languages, $layout, $url)
    {
        self::page($page);

        Language::validateLanguages($languages);

        Layout::validateLayout($layout);

        self::url($url);
    }

    /**
     * Validate a page name
     *
     * @param string $value
     * @throws Exception
     * @return bool
     */
    public static function page($value)
    {
        // Make sure this matches the Javascript validation
        if (!preg_match("/^[[:alpha:]0-9_ \\.\\-'\"]*$/i", $value)) {
            throw new Exception("Invalid page name");
        }

        return true;
    }

    /**
     * Validate a url
     *
     * @param string $value
     * @throws Exception
     * @return bool
     */
    public static function url($value)
    {
        if (!is_string($value)) {
            throw new Exception("Invalid URL");
        }

        return true;
    }
}