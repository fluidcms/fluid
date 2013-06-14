<?php

namespace Fluid;

/**
 * Route requests to pages.
 *
 * @package fluid
 */
class Router
{
    private static $request;

    /**
     * Route a request
     *
     * @param   string  $request
     * @return  mixed
     */
    public static function route($request = null)
    {
        $request = '/' . ltrim($request, '/');

        if (!$structure = Data::getStructure()) {
            Data::setStructure($structure = new Models\Structure());
        }

        $page = self::matchRequest($request, $structure->pages);

        if (isset($page) && false !== $page) {
            return Page::create($page['layout'], Data::get($page['page']));
        } else {
            return Fluid::NOT_FOUND;
        }
    }

    /**
     * Try to match a request with an array of pages
     *
     * @param   string  $request
     * @param   array   $pages
     * @return  bool
     */
    private static function matchRequest($request, $pages, $parent = '')
    {
        foreach ($pages as $page) {
            if (isset($page['url']) && $request == $page['url']) {
                $page['page'] = trim($parent . '/' . $page['page'], '/');
                return $page;
            } else if (isset($page['pages']) && is_array($page['pages'])) {
                $matchPages = self::matchRequest($request, $page['pages'], trim($parent . '/' . $page['page'], '/'));
                if ($matchPages) {
                    return $matchPages;
                }
            }
        }
        return false;
    }

    /**
     * Get the requested url
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

        return ($secure ? 'https://' : 'http://') . "{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
    }
}