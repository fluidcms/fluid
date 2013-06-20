<?php

namespace Fluid\Models\Page;

use Fluid\Models\Page, Exception;

/**
 * Validate page
 *
 * @package fluid
 */
class Validator
{
    /**
     * Validate a page name
     *
     * @param   string      $page    The page name
     * @throws  Exception   Invalid page name
     * @return  bool
     */
    public static function name($page)
    {
        if (!preg_match("/^[a-z0-9_]*$/i", $page)) {
            throw new Exception("Invalid page name");
        }

        return true;
    }

    /**
     * Validate a page name
     *
     * @param   array       $content    The page content
     * @throws  Exception
     * @return  array
     */
    public static function content($page)
    {
        if (!is_array($page)) {
            throw new Exception("Content is not a valid array");
        }
    }
}